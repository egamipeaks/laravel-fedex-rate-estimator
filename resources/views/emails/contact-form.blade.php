<x-mail::message>
# You have received a new message from your website contact form.

Name: {{ $first_name }} {{ $last_name }}

Company: {{ $company }}

Phone: {{ $phone_number }}

Email: {{ $email }}

Subject: {{ $user_subject }}

Message:
{{ $user_message }}
</x-mail::message>
