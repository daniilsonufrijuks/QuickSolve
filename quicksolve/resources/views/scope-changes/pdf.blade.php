<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Scope change {{ $note['project_name'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.5; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        h2 { font-size: 13px; margin: 16px 0 6px; }
        .muted { color: #475569; margin: 0 0 16px; }
        .box { border: 1px solid #cbd5e1; padding: 10px 12px; margin: 0 0 10px; }
        .body { white-space: pre-wrap; margin: 0; }
        .notice { margin-top: 24px; font-size: 10px; color: #64748b; }
        .hold { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Scope change confirmation</h1>
    <p class="muted">{{ $note['project_name'] }} · {{ $note['from_name'] }} to {{ $note['client_name'] }}</p>
    @if (! empty($note['from_email']))
        <p class="muted">{{ $note['from_email'] }}</p>
    @endif

    <h2>Originally agreed</h2>
    <div class="box"><p class="body">{{ $note['agreed_work'] }}</p></div>

    <h2>Extra request</h2>
    <div class="box"><p class="body">{{ $note['extra_request'] }}</p></div>

    <p>
        Extra cost: {{ $note['extra_amount_display'] }}
        @if (isset($note['extra_hours']) && $note['extra_hours'] !== '' && $note['extra_hours'] !== null)
            · Extra time: {{ $note['extra_hours'] }} hours
        @endif
        @if (! empty($note['new_deadline']))
            · Proposed new date: {{ $note['new_deadline'] }}
        @endif
    </p>

    @if ($note['hold_until_confirmed'])
        <p class="hold">Work on this extra request does not start until you confirm in writing (reply to this note is enough).</p>
    @endif

    <p>If this matches what you asked for, reply “confirmed” and I will add it to the job. If not, say what to drop or change.</p>

    <p class="notice">This is a record of a requested change. It is not a signed contract, not legal advice, and QuickSolve does not send it to the client for you.</p>
</body>
</html>
