<x-mail::message>
# Invitation To Join {{$project->name}} Project
You have been invited to join {{$project->name}} project.
<br>
click below button to join and start working on it.


<x-mail::button :url="config('app.frontend_url')">
Here
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
