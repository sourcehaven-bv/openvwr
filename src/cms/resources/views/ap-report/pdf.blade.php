<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-size: 0.9rem;
        }

        h1, h2, h3 {
            color: #F84F39;
            margin: 1rem 0 0.5rem 0;
        }

        h1 {
            font-size: 1.5rem;
            padding: 0.5rem;
            color: #fff;
            background-color: #F84F39;
            border-radius: 0.2rem;
        }

        h2 {
            font-size: 1.25rem;
            padding: 0.3rem 0.5rem;
            background-color: #fedcd7;
            border-radius: 0.2rem;
        }

        h3 { font-size: 1.05rem; }

        p { margin: 0.2rem 0; }
        ul { margin: 0.2rem 0; padding-left: 1.2rem; }

        .intro { color: #444; }

        .answer {
            margin: 0.6rem 0;
            padding: 0.4rem 0.6rem;
            border-left: 3px solid #ddd;
        }

        /* The three answer kinds must stay apart at a glance: what the register
           states, what is only a suggestion, and what is still missing. */
        .answer--derived { border-left-color: #e2a33c; background-color: #fdf6e9; }
        .answer--missing { border-left-color: #c23b22; background-color: #fbecea; }

        .answer__question { font-weight: bold; }
        .answer__value--missing { color: #c23b22; font-style: italic; }

        .answer__hint {
            font-size: 0.8rem;
            color: #7a5c1e;
        }

        .answer__source {
            font-size: 0.75rem;
            color: #666;
        }
    </style>
</head>
<body>

@include('ap-report.report', ['report' => $report])

<hr>

<p>{{ config('app.name') }} : {{ __('ap_report.title') }} {{ DateFormat::toDateTime(now()) }}</p>

</body>
</html>
