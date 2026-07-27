<html>
<head>
    <style>
        body {
            font-family: ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
        }

        h1, h2, h3, h4, h5, h6 {
            color: #F84F39;
            margin: 1rem 0 0.5rem 0;
        }

        h1 {
            font-size: 1.5rem;
            padding: 0.5rem;
            background-color: #fedcd7;
            border-radius: 0.2rem;
        }

        body > h1:first-of-type {
            color: #fff;
            background-color: #F84F39;
        }

        h2 { font-size: 1.4rem; }
        h3 { font-size: 1.3rem; }
        h4 { font-size: 1.2rem; }
        h5 { font-size: 1.1rem; }
        h6 { font-size: 1rem; }
        ul { margin-top: 0; }
    </style>
</head>
<body>

    <h1>Verwerkingsregister: {{ __(sprintf('%s.model_singular', Str::snake(class_basename($record->snapshot_source_type)))) }}</h1>

    <p>
        <b>{{ __('snapshot.version') }}</b>: {{ $record->version }}<br>
        <b>{{ __('snapshot.state') }}</b>: {{ __(sprintf('snapshot_state.label.%s', $record->state)) }}
    </p>

    {!! $publicMarkdown !!}

    <h1>{{ __('snapshot.private_data') }}</h1>

    {!! $privateMarkdown !!}

    <hr>

    <p>{{ config('app.name') }} : {{ __('snapshot.export_to_pdf') }} {{ DateFormat::toDateTime(now()) }}</p>

</body>