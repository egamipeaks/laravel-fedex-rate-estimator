<tr>
    <td style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;text-align:center;">
        {!! $label->status_icon !!} {{ $label->status->label() }}
    </td>
    <td style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;">
        <a href="{{ $label->tracking_link }}" style="color:#2a6ebb;word-break:break-all;text-decoration:none;">
            {{ $label->tracking_number }}
        </a>
    </td>
    <td style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;">
        <strong>Reference</strong><br>
        {{ $label->reference_number }}<br><br>

        <strong>PO</strong><br>
        {{ $label->purchase_order }}
    </td>
    <td style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;line-height:1.3;word-break:break-word;">
        @if($priceBreakdown)
            <div>
                <div><strong>Base rate:</strong> {{ $priceBreakdown['base_rate'] }}</div>
                @foreach($priceBreakdown['surcharges'] as $title => $amt)
                    <div><strong>{{ $title }}:</strong> {{ $amt }}</div>
                @endforeach
                @foreach($priceBreakdown['discounts'] as $title => $amt)
                    <div><strong>{{ $title }}:</strong> {{ $amt }}</div>
                @endforeach
                <div style="margin-top:4px;border-top:1px solid #ccc;padding-top:4px;">
                    <strong>Total:</strong> {{ $priceBreakdown['estimated_total'] }}
                </div>
            </div>
        @else
            <em>No estimate</em>
        @endif
    </td>
    <td style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;">
        {{ $label->recipient_name }}<br>
        {{ $label->recipient_city_state }}
    </td>
    <td style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;">
        {{ $label->weight }}
    </td>
    <td style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;">
        {{ $label->service_type }}
    </td>
    <td style="font-size:13px;padding:8px;border:1px solid #ddd;vertical-align:top;">
        {{ $label->shipper_name }}
    </td>
</tr>
