<table width="100%" class="responsive" cellpadding="0" cellspacing="0" style="max-width:1200px;margin:auto;background:#f7f7f7;border-collapse:collapse;font-family:Arial,sans-serif;">
    <thead>
    <tr style="background:#f0f0f0;">
        <th style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;width:8%;">Status</th>
        <th style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;width:15%;">Tracking</th>
        <th style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;width:10%;">Reference</th>
        <th style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;">Estimate</th>
        <th style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;width:12%;">Recipient</th>
        <th style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;width:8%;">Weight</th>
        <th style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;width:10%;">Service</th>
        <th style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;width:15%;">Shipper</th>
    </tr>
    </thead>

    <tbody>
    @foreach($labels as $label)
        <x-email.fedex.label-row :label="$label" />
    @endforeach
    </tbody>
</table>
