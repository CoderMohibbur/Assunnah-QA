<!doctype html>
<html lang="bn">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Answer Published</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f6f7fb; margin:0; padding:24px;">
  <div style="max-width:640px; margin:0 auto; background:#fff; border-radius:12px; padding:18px;">
    <h2 style="margin:0 0 8px;">আপনার প্রশ্নের উত্তর প্রকাশ হয়েছে ✅</h2>

    <p style="margin:0 0 10px; color:#334155;">
      <strong>প্রশ্ন:</strong> {{ $question->title }}
    </p>

    <p style="margin:0 0 14px; color:#334155;">
      নিচের লিংকে ক্লিক করে উত্তর দেখুন:
    </p>

    @php
      $slug = $question->slug ?: ('q-'.$question->id);
      $url  = url('/questions/'.$slug);
    @endphp

    <p style="margin:0 0 18px;">
      <a href="{{ $url }}" style="display:inline-block; padding:10px 14px; background:#2563eb; color:#fff; border-radius:10px; text-decoration:none;">
        উত্তর দেখুন
      </a>
    </p>

    <hr style="border:none; border-top:1px solid #e5e7eb; margin:18px 0;">

    <p style="margin:0; color:#64748b; font-size:12px;">
      ধন্যবাদ — Q/A সিস্টেম
    </p>
  </div>
</body>
</html>
