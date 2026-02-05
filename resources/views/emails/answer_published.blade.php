<!doctype html>
<html lang="bn">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Answer Published</title>
</head>

<body style="font-family: Arial, sans-serif; background:#f6f7fb; margin:0; padding:24px;">
    <div style="max-width:640px; margin:0 auto; background:#fff; border-radius:12px; padding:18px;">
        <h2 style="margin:0 0 8px;">Your question has been answered ✅</h2>

        <p style="margin:0 0 10px; color:#334155;">
            <strong>Question:</strong> {{ $question->title }}
        </p>

        <p style="margin:0 0 14px; color:#334155;">
            Click the link below to view the answer:
        </p>

        @php
            $slug = trim((string) ($question->slug ?? ''));

            if ($slug === '' || (preg_match('/^q-(\d+)$/', $slug, $m) && (int) $m[1] !== (int) $question->id)) {
                $slug = 'q-' . $question->id;
            }

            $url = route('questions.show', ['slug' => $slug]);
        @endphp


        <p style="margin:0 0 18px;">
            <a href="{{ $url }}"
                style="display:inline-block; padding:10px 14px; background:#2563eb; color:#fff; border-radius:10px; text-decoration:none;">
                View Answer
            </a>
        </p>

        <hr style="border:none; border-top:1px solid #e5e7eb; margin:18px 0;">

        <p style="margin:0; color:#64748b; font-size:12px;">
            Jazak Allah Khair
        </p>
    </div>
</body>

</html>
