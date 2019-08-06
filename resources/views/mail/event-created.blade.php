@component('mail::message')
# Introduction

Important Notification<br>
{{$proj}}<br>
{{$project}}

@component('mail::button', ['url' => ''])
Click to accept invitation
@endcomponent

PS. Clicking the button does nothing<br>
Thanks,<br>
{{ "Sampriti" }}
@endcomponent
