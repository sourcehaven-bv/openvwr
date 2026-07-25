{{--
    Quick-jump navigation for the one-page register layout.

    The section list is built from the DOM rather than passed in: the sections
    are already marked with data-onepage-section, and reading them client-side
    keeps this working for both the form (edit) and the infolist (view) without
    either of them having to hand over their schema.

    Section tracking uses IntersectionObserver so the active item stays correct
    while scrolling fast, which is what the layout is for.
--}}
<div
    x-data="{
        sections: [],
        active: null,

        observer: null,

        init: function () {
            this.$nextTick(() => this.collect())

            // Conditional fields mean sections can appear or disappear on a
            // Livewire round trip; rebuild the list so the nav keeps matching
            // what is actually on the page.
            Livewire.hook('morphed', () => this.collect())
        },

        collect: function () {
            const layout = document.querySelector('.onepage-layout')

            if (! layout) {
                return
            }

            const elements = layout.querySelectorAll('[data-onepage-section]')

            this.sections = Array.from(elements).map((element) => {
                // Direct child header only: sections contain nested information
                // blocks, whose headings would otherwise win the lookup.
                const heading = element.querySelector(
                    ':scope > .fi-section-header .fi-section-header-heading',
                )

                return {
                    key: element.dataset.onepageSection,
                    label: heading ? heading.textContent.trim() : element.dataset.onepageSection,
                    element: element,
                }
            })

            if (! this.sections.length) {
                return
            }

            if (this.active === null) {
                this.active = this.sections[0].key
            }

            // Drop the previous observer so re-collecting does not leave stale
            // elements being watched.
            this.observer?.disconnect()

            this.observer = new IntersectionObserver(
                (entries) => {
                    entries
                        .filter((entry) => entry.isIntersecting)
                        .forEach((entry) => {
                            this.active = entry.target.dataset.onepageSection
                        })
                },
                {
                    // Only count a section as active once it reaches the upper
                    // part of the viewport, so the highlight tracks what is
                    // being read rather than whatever is entering at the bottom.
                    rootMargin: '-80px 0px -70% 0px',
                    threshold: 0,
                },
            )

            this.sections.forEach((section) => this.observer.observe(section.element))
        },

        jumpTo: function (section) {
            section.element.scrollIntoView({ behavior: 'smooth', block: 'start' })
            this.active = section.key
        },
    }"
    class="onepage-nav"
    role="navigation"
    aria-label="{{ __('general.onepage_nav_label') }}"
>
    <template x-for="section in sections" :key="section.key">
        <button
            type="button"
            class="onepage-nav__item"
            :class="{ 'fi-active': active === section.key }"
            :aria-current="active === section.key ? 'true' : null"
            x-on:click="jumpTo(section)"
            x-text="section.label"
        ></button>
    </template>
</div>
