@props(['outcome'])

@php($value = $outcome instanceof \App\Modules\Reminders\Enums\ReminderOutcome ? $outcome : \App\Modules\Reminders\Enums\ReminderOutcome::from($outcome))

<span {{ $attributes->class([
    'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
    'bg-sky-100 text-sky-800' => $value === \App\Modules\Reminders\Enums\ReminderOutcome::Processing,
    'bg-amber-100 text-amber-900' => $value === \App\Modules\Reminders\Enums\ReminderOutcome::Omitted,
    'bg-emerald-100 text-emerald-800' => $value === \App\Modules\Reminders\Enums\ReminderOutcome::Accepted,
    'bg-red-100 text-red-800' => $value === \App\Modules\Reminders\Enums\ReminderOutcome::Failed,
]) }}>{{ $value->label() }}</span>
