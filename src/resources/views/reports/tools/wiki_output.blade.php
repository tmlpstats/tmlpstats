Report | Internal Name | Ticket | Questions
--- | --- | --- | ---
@foreach ($bucket->reports as $report)
{{ $report->name }}|[{{$report->id }}](#{{ $report->id }})|{{ $report->ticket_link }}|{{ (count($report->questions) > 0)? 'yes' : 'no' }}
@endforeach

@foreach ($bucket->reports as $report)
<a name="{{ $report->id }}" id="{{ $report->id }}"></a>
### {{ $report->name }}
```yaml
id: {{ $report->id }}
scope: {{ $report->scope->id }}
access: {{ implode(', ', $report->access) }}
```
@if (count($report->questions) > 0)
**Questions:**
@foreach ($report->questions as $question)
 * {{ $question }}
@endforeach

@endif
{!! $report->desc !!}

@endforeach