/**
 * Annotation overlay, injected into the page by Playwright via addInitScript().
 *
 * Deliberately NOT part of the application bundle: nothing here ships to
 * production, so there is no way for a stray URL param to render arrows for
 * real users. The capture script owns this code.
 *
 * Exposes window.__annotate with helpers that anchor to CSS selectors rather
 * than pixel coordinates, so a UI change misplaces nothing silently - a moved
 * element throws instead.
 */
(() => {
  const ACCENT = '#F84F39'; // OpenVWR huisstijl

  const LAYER_ID = '__annotation_layer';

  function layer() {
    let el = document.getElementById(LAYER_ID);
    if (!el) {
      el = document.createElement('div');
      el.id = LAYER_ID;
      Object.assign(el.style, {
        position: 'absolute',
        inset: '0',
        pointerEvents: 'none',
        zIndex: '2147483647',
      });
      document.body.appendChild(el);
    }
    return el;
  }

  function resolve(target) {
    const el = typeof target === 'string' ? document.querySelector(target) : target;
    if (!el) throw new Error(`annotate: no element for selector ${target}`);
    const r = el.getBoundingClientRect();
    if (r.width === 0 && r.height === 0) {
      throw new Error(`annotate: element has zero size: ${target}`);
    }
    // Page coordinates: the layer is absolutely positioned in the document,
    // so scroll offset must be added to viewport-relative rects.
    return {
      left: r.left + window.scrollX,
      top: r.top + window.scrollY,
      width: r.width,
      height: r.height,
      cx: r.left + window.scrollX + r.width / 2,
      cy: r.top + window.scrollY + r.height / 2,
    };
  }

  /**
   * Arrow pointing at an element. `side` is which side of the target the arrow
   * sits on, so the arrow always points *towards* the target:
   *   left  -> points right    right -> points left
   *   top   -> points down     bottom -> points up
   * All four occur in the existing handleiding.
   */
  function arrow(
    target,
    {
      side = 'left',
      length = 190,
      thickness = 18,
      gap = 14,
      // Black with a white outline: readable on light backgrounds and on the
      // primary buttons, which are themselves the accent colour.
      color = '#111827',
      outlineColor = '#fff',
      outlineWidth = 3,
    } = {},
  ) {
    const t = resolve(target);
    const head = thickness * 2.1;
    const horizontal = side === 'left' || side === 'right';

    // Long axis follows the pointing direction; short axis holds the head.
    const svgW = horizontal ? length : head;
    const svgH = horizontal ? head : length;

    let x, y;
    if (side === 'left')        { x = t.left - gap - length;      y = t.cy - svgH / 2; }
    else if (side === 'right')  { x = t.left + t.width + gap;     y = t.cy - svgH / 2; }
    else if (side === 'top')    { x = t.cx - svgW / 2;            y = t.top - gap - length; }
    else                        { x = t.cx - svgW / 2;            y = t.top + t.height + gap; }

    // An arrow placed off-canvas is a silent failure: it would simply be
    // cropped out of the screenshot. Fail loudly so the caller shortens the
    // arrow or picks another side.
    // Overflowing right/bottom is just as bad as negative: fullPage capture
    // silently grows the canvas to fit the arrow, so the image gets a band of
    // empty space and no longer matches the other figures.
    const maxX = document.documentElement.scrollWidth;
    if (x < 0 || y < 0 || x + svgW > maxX) {
      throw new Error(
        `annotate: arrow for ${target} on side "${side}" lands off-canvas ` +
        `(x=${Math.round(x)}..${Math.round(x + svgW)}, y=${Math.round(y)}, page width ${maxX}). ` +
        `Reduce length or use another side.`
      );
    }

    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('width', svgW);
    svg.setAttribute('height', svgH);
    svg.setAttribute('viewBox', `0 0 ${svgW} ${svgH}`);
    Object.assign(svg.style, {
      position: 'absolute',
      left: `${x}px`,
      top: `${y}px`,
      overflow: 'visible',
    });

    // Tip sits at the end nearest the target; tail at the far end.
    const mid = horizontal ? svgH / 2 : svgW / 2;
    const span = horizontal ? svgW : svgH;
    const forward = side === 'left' || side === 'top'; // tip at the high end
    const tip = forward ? span : 0;
    const base = forward ? span - head * 0.62 : head * 0.62;
    const tail = forward ? 0 : span;

    // One continuous polygon rather than a rect plus a triangle, so the outline
    // below traces a single outer edge.
    const h = thickness / 2;
    const hh = head / 2;
    const pts = horizontal
      ? [
          [tail, mid - h],
          [base, mid - h],
          [base, mid - hh],
          [tip, mid],
          [base, mid + hh],
          [base, mid + h],
          [tail, mid + h],
        ]
      : [
          [mid - h, tail],
          [mid - h, base],
          [mid - hh, base],
          [mid, tip],
          [mid + hh, base],
          [mid + h, base],
          [mid + h, tail],
        ];

    const arrowEl = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
    arrowEl.setAttribute('points', pts.map((p) => p.join(',')).join(' '));
    arrowEl.setAttribute('fill', color);
    // White outline around the whole shape. Because this is one polygon rather
    // than a rect plus a triangle, the stroke traces the outer edge only - two
    // overlapping shapes would each stroke their shared edge and leave a seam.
    // paint-order puts the stroke behind the fill so it reads as a border.
    arrowEl.setAttribute('stroke', outlineColor);
    arrowEl.setAttribute('stroke-width', String(outlineWidth));
    arrowEl.setAttribute('stroke-linejoin', 'round');
    arrowEl.setAttribute('paint-order', 'stroke');

    svg.append(arrowEl);
    layer().appendChild(svg);
    return svg;
  }

  /** Rounded outline box around an element. */
  function box(target, { pad = 6, color = ACCENT, width = 4, radius = 8 } = {}) {
    const t = resolve(target);
    const el = document.createElement('div');
    Object.assign(el.style, {
      position: 'absolute',
      left: `${t.left - pad}px`,
      top: `${t.top - pad}px`,
      width: `${t.width + pad * 2}px`,
      height: `${t.height + pad * 2}px`,
      border: `${width}px solid ${color}`,
      borderRadius: `${radius}px`,
      boxSizing: 'border-box',
    });
    layer().appendChild(el);
    return el;
  }

  /**
   * Numbered badge, for multi-step figures. `place` positions it relative to
   * the target: 'outside-left' keeps it clear of the element (the usual want),
   * 'corner' overlaps the top-left corner.
   */
  function badge(target, n, { size = 34, color = ACCENT, place = 'outside-left', gap = 10 } = {}) {
    const t = resolve(target);
    const offset =
      place === 'corner'
        ? { x: -size / 3, y: -size / 3 }
        : { x: -(size + gap), y: (t.height - size) / 2 };
    const el = document.createElement('div');
    el.textContent = String(n);
    Object.assign(el.style, {
      position: 'absolute',
      left: `${t.left + offset.x}px`,
      top: `${t.top + offset.y}px`,
      width: `${size}px`,
      height: `${size}px`,
      borderRadius: '50%',
      background: color,
      color: '#fff',
      font: `600 ${Math.round(size * 0.55)}px/${size}px ui-sans-serif, system-ui, sans-serif`,
      textAlign: 'center',
      boxShadow: '0 1px 3px rgba(0,0,0,.3)',
    });
    layer().appendChild(el);
    return el;
  }

  /** Redact a region (for anything resembling personal data). */
  function redact(target, { color = '#94a3b8', radius = 4 } = {}) {
    const t = resolve(target);
    const el = document.createElement('div');
    Object.assign(el.style, {
      position: 'absolute',
      left: `${t.left}px`,
      top: `${t.top}px`,
      width: `${t.width}px`,
      height: `${t.height}px`,
      background: color,
      borderRadius: `${radius}px`,
    });
    layer().appendChild(el);
    return el;
  }

  /**
   * Cover an element with an opaque placeholder carrying its own label, for
   * content that must not be published at all - a QR code that encodes a TOTP
   * secret, say. Sized from the element, so a wider or wrapped target is
   * covered in full without anyone re-measuring anything.
   *
   * Unlike redact() this is not a grey bar: it explains itself, so a reader who
   * meets the figure without the surrounding text understands that a real code
   * appears here rather than a rendering failure.
   *
   * The mask is painted *before* the screenshot, so the secret is never in the
   * captured image - there is no reversible transform to undo, and nothing to
   * scrub afterwards. Callers should verify coverage with masked() rather than
   * assume a selector still matches what they think it does.
   */
  function mask(
    target,
    text = '',
    // `box` draws the framed placeholder, which reads as "something belongs
    // here". Pass box: false for a masked line of text, where a frame around a
    // single line looks heavier than the text it replaces.
    { background = '#f3f4f6', border = '#9ca3af', color = '#4b5563', box = true } = {},
  ) {
    const t = resolve(target);
    const el = document.createElement('div');
    const style = window.getComputedStyle(
      typeof target === 'string' ? document.querySelector(target) : target,
    );
    Object.assign(el.style, {
      position: 'absolute',
      left: `${t.left}px`,
      top: `${t.top}px`,
      width: `${t.width}px`,
      // Let a text mask grow if its replacement text needs more room than the
      // original: the point is to cover, and a clipped placeholder would leave
      // the real content peeking out below.
      minHeight: `${t.height}px`,
      background: box ? background : '#ffffff',
      border: box ? `2px solid ${border}` : 'none',
      borderRadius: box ? '4px' : '0',
      boxSizing: 'border-box',
      display: 'flex',
      alignItems: 'center',
      justifyContent: box ? 'center' : 'flex-start',
      textAlign: box ? 'center' : 'left',
      whiteSpace: 'pre-line',
      padding: box ? '4px' : '0',
      color,
      // Inherit the target's own typography so a masked line of text sits on
      // the page exactly as the real one did.
      font: style.font,
      fontFamily: style.fontFamily,
      fontSize: box ? '15px' : style.fontSize,
      lineHeight: box ? '1.3' : style.lineHeight,
    });
    el.textContent = text;
    layer().appendChild(el);
    return el;
  }

  /**
   * Is every element matching `selector` fully covered by a mask?
   *
   * A mask that silently lands on the wrong element is worse than none: the
   * figure ships the secret while the capture reports success. So this checks
   * the actual geometry rather than trusting that a selector still matches.
   */
  function masked(selector) {
    const masks = [...layer().children].map((m) => m.getBoundingClientRect());
    const targets = [...document.querySelectorAll(selector)];
    if (targets.length === 0) return false;
    return targets.every((el) => {
      const r = el.getBoundingClientRect();
      if (r.width === 0 && r.height === 0) return true;
      // Allow a sub-pixel slack: fractional layout rounding otherwise reports a
      // fully covered element as exposed by a hairline.
      const pad = 1;
      return masks.some(
        (m) =>
          m.left <= r.left + pad &&
          m.top <= r.top + pad &&
          m.right >= r.right - pad &&
          m.bottom >= r.bottom - pad,
      );
    });
  }

  function clear() {
    const el = document.getElementById(LAYER_ID);
    if (el) el.remove();
  }

  window.__annotate = { arrow, box, badge, redact, mask, masked, clear, ACCENT };
})();
