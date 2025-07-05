<x-mail::message>

# FedEx Import Summary

Here's the summary of the most recent FedEx import:

---

## 🆕 New Labels ({{ $newLabels->count() }})

@foreach($newLabels as $label)
- [{{ $label->tracking_number }}](https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers={{ $label->tracking_number }}&clienttype=ivother) (status: {{ $label->status }})
@endforeach

---

## ✏️ Updated Labels ({{ $updatedLabels->count() }})

@foreach($updatedLabels as $label)
- [{{ $label->tracking_number }}](https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers={{ $label->tracking_number }}&clienttype=ivother) (status: {{ $label->status }})
@endforeach

---

## 💵 New Rate Estimates ({{ $newEstimates->count() }})

@foreach($newEstimates as $estimate)
- [{{ $estimate->label->tracking_number }}](https://www.fedex.com/apps/fedextrack/?action=track&tracknumbers={{ $estimate->label->tracking_number }}&clienttype=ivother) ({{ $estimate->service_type }}): ${{ number_format($estimate->estimate / 100, 2) }}
@endforeach

</x-mail::message>
