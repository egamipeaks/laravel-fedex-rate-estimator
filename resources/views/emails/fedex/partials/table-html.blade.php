@php
    $tableStyle = 'width: 100%; border-collapse: collapse; margin-bottom: 30px;';
    $thStyle = 'padding: 8px 12px; background-color: #f5f5f5; border: 1px solid #ddd; text-align: left;';
    $tdStyle = 'padding: 8px 12px; border: 1px solid #ddd;';
    $nestedTableStyle = 'width:100%; border-collapse: collapse; margin-top:10px;';
    $nestedTdStyle = 'padding:4px; border: 1px solid #ddd;';
@endphp

<table style="{{ $tableStyle }}">
    <thead>
    <tr>
        <th style="{{ $thStyle }}">Status</th>
        <th style="{{ $thStyle }}">Tracking #</th>
        <th style="{{ $thStyle }}">Reference #</th>
        <th style="{{ $thStyle }}">Estimate</th>
        <th style="{{ $thStyle }}">Recipient</th>
        <th style="{{ $thStyle }}">City/State</th>
        <th style="{{ $thStyle }}">Weight</th>
        <th style="{{ $thStyle }}">Service</th>
        <th style="{{ $thStyle }}">Shipper</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($labels as $label)
        <tr>
            <td style="{{ $tdStyle }}">{{ $label->status_icon }} {{ $label->status->label() }}</td>
            <td style="{{ $tdStyle }}">
                <a href="{{ $label->tracking_link }}" style="color: #2a6ebb;">
                    {{ $label->tracking_number }}
                </a>
            </td>
            <td style="{{ $tdStyle }}">{{ $label->reference }}</td>
            <td style="{{ $tdStyle }}">
                @if($label->latestEstimate)
                    @php
                        $formattedEstimate = number_format($label->latestEstimate->estimate / 100, 2);
                        $serviceType = $label->latestEstimate->service_type;
                        $breakdown = $label->latestEstimate->getFormattedPriceBreakdown();
                    @endphp

                    <small class="text-muted">{{ $serviceType }}</small>

                    @if(!empty($breakdown))
                        <div style="background: #fafafa; border: 1px solid #ddd; padding: 8px; margin-top: 8px; border-radius: 4px; font-size: 90%;">
                            @if(isset($breakdown['base_rate']))
                                <div style="margin-bottom: 4px;">
                                    <strong>Base rate:</strong> {{ $breakdown['base_rate'] }}
                                </div>
                            @endif

                            @if(!empty($breakdown['surcharges']))
                                @foreach($breakdown['surcharges'] as $desc => $amount)
                                    <div style="margin-bottom: 4px;">
                                        <strong>{{ $desc }}:</strong> {{ $amount }}
                                    </div>
                                @endforeach
                            @endif

                            @if(!empty($breakdown['discounts']))
                                @foreach($breakdown['discounts'] as $desc => $amount)
                                    <div style="margin-bottom: 4px;">
                                        <strong>{{ $desc }}:</strong> {{ $amount }}
                                    </div>
                                @endforeach
                            @endif

                            <hr style="border:none; border-top:1px solid #ccc; margin:8px 0;">

                            @if(isset($breakdown['estimated_total']))
                                <div style="margin-bottom: 0;">
                                    <strong>Estimated Total: {{ $breakdown['estimated_total'] }}</strong>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <em>No estimate</em>
                @endif
            </td>
            <td style="{{ $tdStyle }}">{{ $label->recipient_name }}</td>
            <td style="{{ $tdStyle }}">{{ $label->recipient_city_state }}</td>
            <td style="{{ $tdStyle }}">{{ $label->weight }}</td>
            <td style="{{ $tdStyle }}">{{ $label->service_type }}</td>
            <td style="{{ $tdStyle }}">{{ $label->shipper_name }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
