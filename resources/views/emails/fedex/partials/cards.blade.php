@php
    // simple helper to break long tracking links
    $linkStyle = 'color:#2a6ebb;word-break:break-all;text-decoration:none;';
    $cardStyle = 'background:#fff;border:1px solid #ddd;border-radius:6px;overflow:hidden;margin-bottom:16px;';
    $cellStyle = 'padding:12px;border-bottom:1px solid #eee;';
@endphp

@foreach($labels as $label)
    <table class="card" width="100%" cellpadding="0" cellspacing="0" style="{{ $cardStyle }}">
        <tr>
            <td style="{{ $cellStyle }}font-weight:bold;">
                {{ $label->status_icon }} {{ $label->status->label() }}
            </td>
        </tr>
        <tr>
            <td style="{{ $cellStyle }}">
                <strong>Tracking #:</strong>
                <a href="{{ $label->tracking_link }}" style="{{ $linkStyle }}">
                    {{ $label->tracking_number }}
                </a>
            </td>
        </tr>
        <tr>
            <td style="{{ $cellStyle }}">
                <strong>Reference #:</strong> {{ $label->reference }}
            </td>
        </tr>
        <tr>
            <td style="{{ $cellStyle }}">
                <strong>Estimate:</strong><br>
                @if($label->latestEstimate)
                    @php
                        $breakdown = $label->latestEstimate->getFormattedPriceBreakdown();
                    @endphp
                    <small>{{ $label->latestEstimate->service_type }}</small>
                    <div style="
                      background: #fafafa;
                      border: 1px solid #ddd;
                      border-radius: 6px;
                      padding: 16px;
                      font-size: 16px;           /* bump the base font */
                      line-height: 1.4;          /* breathing room between lines */
                      /*max-width: 280px;          !* optional: limit width on desktop *!*/
                      margin: 8px auto;          /* center on mobile */
                    ">
                        <p style="margin:0 0 8px;">
                            <strong>Base rate:</strong> {{ $breakdown['base_rate'] }}
                        </p>

                        @foreach($breakdown['surcharges'] as $title => $amount)
                            <p style="margin:0 0 8px;">
                                <strong>{{ $title }}:</strong> {{ $amount }}
                            </p>
                        @endforeach

                        @foreach($breakdown['discounts'] as $title => $amount)
                            <p style="margin:0 0 {{ $loop->last ? '12px' : '8px' }};">
                                <strong>{{ $title }}:</strong> {{ $amount }}
                            </p>
                        @endforeach

                        <hr style="border:none;border-top:1px solid #ccc;margin:0 0 12px;">

                        <p style="margin:0;">
                            <strong>Estimated Total:</strong> {{ $breakdown['estimated_total'] }}
                        </p>

                    </div>
                @else
                    <em>No estimate</em>
                @endif
            </td>
        </tr>
        <tr>
            <td style="{{ $cellStyle }}">
                <strong>Recipient:</strong> {{ $label->recipient_name }}
            </td>
        </tr>
        <tr>
            <td style="{{ $cellStyle }}">
                <strong>Location:</strong> {{ $label->recipient_city_state }}
            </td>
        </tr>
        <tr>
            <td style="{{ $cellStyle }}">
                <strong>Weight:</strong> {{ $label->weight }}
            </td>
        </tr>
        <tr>
            <td style="{{ $cellStyle }}">
                <strong>Service:</strong> {{ $label->service_type }}
            </td>
        </tr>
        <tr>
            <td style="padding:12px;">
                <strong>Shipper:</strong> {{ $label->shipper_name }}
            </td>
        </tr>
    </table>
@endforeach
