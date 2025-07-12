@component('mail::message')
# Document Expiry Reminder

@if($daysUntilExpiry == 1)
**URGENT: Documents Expiring Tomorrow**
@else
**Documents Expiring in {{ $daysUntilExpiry }} days**
@endif

@if($recipientType == 'admin')
The following documents in your company are approaching their expiry date:
@else
The following documents related to you are approaching their expiry date:
@endif

@component('mail::table')
| Document Type | Subject | Issue Date | Expiry Date |
| ------------- | ------- | ---------- | ----------- |
@foreach($documents as $doc)
| {{ $doc['document_type'] }} | {{ $doc['subject_name'] }} | {{ $doc['issue_date'] }} | {{ $doc['expiry_date'] }} |
@endforeach
@endcomponent

@if($recipientType == 'admin')
Please ensure these documents are renewed before they expire.
@else
Please submit updated documents before the expiry date.
@endif

@component('mail::button', ['url' => config('app.url') . '/documents'])
View Documents
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
