<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>FedEx Shipment Summary</title>
</head>
<body style="font-family:Arial,sans-serif;padding:20px;color:#333;">

<table width="100%" cellpadding="0" cellspacing="0" style="max-width:1200px;margin:auto;">
    <tr>
        <td>
            <h2 style="margin:0 0 16px;">📦 FedEx Shipment Summary</h2>

            @if($newLabels->count())
                <h3 style="margin:0 0 12px;font-size:18px;">🆕 New Labels</h3>
                <x-email.fedex.label-summary-table :labels="$newLabels" />
            @endif

            @if($updatedLabels->count())
                <h3 style="margin:24px 0 12px;font-size:18px;">🔁 Updated Labels</h3>
                <x-email.fedex.label-summary-table :labels="$updatedLabels" />
            @endif
        </td>
    </tr>
</table>

</body>
</html>
