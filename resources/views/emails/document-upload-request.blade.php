@component('mail::message')
# Document Upload Request

Hello,

You've received a request to upload a document for **{{ $uploadRequest->subject->name }}**.

@if($uploadRequest->documentType)
**Document Type:** {{ $uploadRequest->documentType->name }}
@endif

To upload the document, please use the link below:

@component('mail::button', ['url' => $url])
Upload Document
@endcomponent

You will need this verification code to complete your upload:

@component('mail::panel')
**{{ $uploadRequest->verification_code }}**
@endcomponent

This link will expire {{ $expiryTime }}.

If you have any questions or didn't request this document upload, please contact us.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
