<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FedEx Shipment Summary</title>
    <style>
        /* force full‑width on mobile */
        @media only screen and (max-width: 480px) {
            .card { display: block !important; width: 100% !important; }
            .card td { display: block !important; width: 100% !important; }
            .card td + td { margin-top: 8px !important; }
        }
    </style>
</head>
<body style="font-family:Arial,sans-serif;background:#f7f7f7;padding:20px;color:#333;">

<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:auto;">
    <tr>
        <td>
            <h2 style="margin:0 0 16px;">📦 FedEx Shipment Summary</h2>

            @if($newLabels->count())
                <h3 style="margin:0 0 12px;font-size:18px;">🆕 New Labels</h3>
                @include('emails.fedex.partials.cards', ['labels' => $newLabels])
            @endif

            @if($updatedLabels->count())
                <h3 style="margin:24px 0 12px;font-size:18px;">🔁 Updated Labels</h3>
                @include('emails.fedex.partials.cards', ['labels' => $updatedLabels])
            @endif
        </td>
    </tr>
</table>

</body>
</html>
