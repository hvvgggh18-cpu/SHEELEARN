@extends('admin.layout')
@section('title', 'Settings | SHEELEARN')
@section('page_title', 'Settings')
@section('page_breadcrumb', 'System')

@section('content')
<div class="space-y-5">
    <div class="ad-tabs mb-2">
        <button class="ad-tab active" data-st="general">General</button>
        <button class="ad-tab" data-st="ai">AI Config</button>
        <button class="ad-tab" data-st="security">Security</button>
    </div>

    <div class="ad-tab-panel active" id="st-general">
        <div class="ad-card p-5 max-w-2xl">
            <div class="ad-eyebrow">Platform</div>
            <div class="ad-heading mb-5">General Settings</div>
            <form id="generalForm" method="patch" class="space-y-4">
                <div><label class="ad-label">Platform Name</label><input type="text" name="platform_name" class="ad-input" value="{{ $settings['platform_name'] ?? 'SHEELEARN' }}" required></div>
                <div><label class="ad-label">Support Email</label><input type="email" name="support_email" class="ad-input" value="{{ $settings['support_email'] ?? 'dasinagee2@gmail.com' }}" required></div>
                <input type="hidden" name="maintenance_mode" value="{{ $settings['maintenance_mode'] ? 1 : 0 }}">
                <input type="hidden" name="allow_registrations" value="{{ $settings['allow_registrations'] ? 1 : 0 }}">
                <div class="flex items-center justify-between p-3 rounded-xl" style="border:1px solid var(--ad-border); background:var(--ad-surface);">
                    <div><p class="text-sm font-semibold" style="color:var(--ad-t1)">Maintenance Mode</p><p class="text-xs" style="color:var(--ad-t3)">Disable platform access for non-admins</p></div>
                    <button type="button" class="ad-toggle {{ $settings['maintenance_mode'] ? 'on' : '' }}" data-setting="maintenance_mode"></button>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl" style="border:1px solid var(--ad-border); background:var(--ad-surface);">
                    <div><p class="text-sm font-semibold" style="color:var(--ad-t1)">Allow Registrations</p><p class="text-xs" style="color:var(--ad-t3)">New users can sign up</p></div>
                    <button type="button" class="ad-toggle {{ $settings['allow_registrations'] ? 'on' : '' }}" data-setting="allow_registrations"></button>
                </div>
                <div><button type="submit" class="ad-btn ad-btn-primary"><i class="fa-solid fa-check text-xs"></i> Save Settings</button></div>
            </form>
        </div>
    </div>

    <div class="ad-tab-panel" id="st-ai">
        <div class="ad-card p-5 max-w-2xl">
            <div class="ad-eyebrow">AI</div>
            <div class="ad-heading mb-5">AI Configuration</div>
            <form id="aiForm" method="patch" class="space-y-4">
                <div><label class="ad-label">Default Model</label><select name="default_model" class="ad-input ad-input-select">
                    <option value="GPT-4o" {{ ($settings['default_model'] ?? 'GPT-4o') === 'GPT-4o' ? 'selected' : '' }}>GPT-4o</option>
                    <option value="Claude 3.5" {{ ($settings['default_model'] ?? '') === 'Claude 3.5' ? 'selected' : '' }}>Claude 3.5</option>
                    <option value="Gemini Pro" {{ ($settings['default_model'] ?? '') === 'Gemini Pro' ? 'selected' : '' }}>Gemini Pro</option>
                </select></div>
                <div><label class="ad-label">Max Tokens per Request</label><input type="number" name="max_tokens" class="ad-input" value="{{ $settings['max_tokens'] ?? 4096 }}"></div>
                <div><label class="ad-label">Temperature</label><input type="number" name="temperature" class="ad-input" value="{{ $settings['temperature'] ?? 0.7 }}" step="0.1" min="0" max="2"></div>
                <div class="flex items-center justify-between p-3 rounded-xl" style="border:1px solid var(--ad-border); background:var(--ad-surface);">
                    <div><p class="text-sm font-semibold" style="color:var(--ad-t1)">Rate Limiting</p><p class="text-xs" style="color:var(--ad-t3)">Limit requests per user per minute</p></div>
                    <button type="button" class="ad-toggle {{ $settings['allow_registrations'] ? 'on' : '' }}" data-setting="allow_registrations"></button>
                </div>
                <div><button type="submit" class="ad-btn ad-btn-primary"><i class="fa-solid fa-check text-xs"></i> Save AI Settings</button></div>
            </form>
        </div>
    </div>

    <div class="ad-tab-panel" id="st-security">
        <div class="ad-card p-5 max-w-2xl">
            <div class="ad-eyebrow">Protection</div>
            <div class="ad-heading mb-5">Security Settings</div>
            <form id="securityForm" method="patch" class="space-y-4">
                <input type="hidden" name="two_factor_auth" value="{{ $settings['two_factor_auth'] ? 1 : 0 }}">
                <input type="hidden" name="session_timeout" value="{{ $settings['session_timeout'] ? 1 : 0 }}">
                <div class="flex items-center justify-between p-3 rounded-xl" style="border:1px solid var(--ad-border); background:var(--ad-surface);">
                    <div><p class="text-sm font-semibold" style="color:var(--ad-t1)">Two-Factor Authentication</p><p class="text-xs" style="color:var(--ad-t3)">Require 2FA for all admin accounts</p></div>
                    <button type="button" class="ad-toggle {{ $settings['two_factor_auth'] ? 'on' : '' }}" data-setting="two_factor_auth"></button>
                </div>
                <div class="flex items-center justify-between p-3 rounded-xl" style="border:1px solid var(--ad-border); background:var(--ad-surface);">
                    <div><p class="text-sm font-semibold" style="color:var(--ad-t1)">Session Timeout</p><p class="text-xs" style="color:var(--ad-t3)">Auto-logout after inactivity</p></div>
                    <button type="button" class="ad-toggle {{ $settings['session_timeout'] ? 'on' : '' }}" data-setting="session_timeout"></button>
                </div>
                <div><label class="ad-label">Session Duration (minutes)</label><input type="number" name="session_duration" class="ad-input" value="{{ $settings['session_duration'] ?? 120 }}"></div>
                <div><label class="ad-label">Max Login Attempts</label><input type="number" name="max_login_attempts" class="ad-input" value="{{ $settings['max_login_attempts'] ?? 5 }}"></div>
                <div><button type="submit" class="ad-btn ad-btn-primary"><i class="fa-solid fa-check text-xs"></i> Save Security Settings</button></div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    $$('.ad-tab[data-st]').forEach(tab => {
        tab.addEventListener('click', () => {
            $$('.ad-tab[data-st]').forEach(t => t.classList.remove('active'));
            $$('.ad-tab-panel[id^="st-"]').forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            $('st-' + tab.dataset.st)?.classList.add('active');
        });
    });

    ['generalForm','aiForm','securityForm'].forEach(id => {
        $(id)?.addEventListener('submit', async e => {
            e.preventDefault();
            updateToggleValues(e.target);
            await adSubmit(e.target, '{{ route("admin.settings.update") }}', e.target.querySelector('button[type="submit"]'));
        });
    });

    function updateToggleValues(form) {
        form.querySelectorAll('button[data-setting]').forEach(button => {
            const name = button.dataset.setting;
            const input = form.querySelector(`input[name="${name}"]`);
            if (!input) return;
            input.value = button.classList.contains('on') ? 1 : 0;
        });
    }

    document.querySelectorAll('button[data-setting]').forEach(button => {
        button.addEventListener('click', () => {
            button.classList.toggle('on');
            const form = button.closest('form');
            if (!form) return;
            const name = button.dataset.setting;
            const input = form.querySelector(`input[name="${name}"]`);
            if (input) {
                input.value = button.classList.contains('on') ? 1 : 0;
            }
        });
    });
});
</script>
@endsection