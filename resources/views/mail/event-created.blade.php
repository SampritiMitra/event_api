@component('mail::message')
# Introduction

You have been invited!!
{{$project}}

@component('mail::button', ['url' => ''])
Click to accept invitation
@endcomponent

PS. Clicking the button does nothing<br>
Thanks,<br>
{{ "Sampriti" }}
@endcomponent
