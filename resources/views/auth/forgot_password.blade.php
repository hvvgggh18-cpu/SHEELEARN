@extends('welcome')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="auth-panel w-full max-w-md p-8">
        <h2 class="text-2xl font-black text-c text-center mb-4">Forgot Your Password?</h2>
        <p class="text-center text-c-25 mb-6">Enter the Gmail address associated with your SHEELEARN account.</p>
        <form id="forgotForm" onsubmit="submitForgot(event)">
            <div>
                <label class="block text-xs font-bold text-c-40 uppercase mb-2">Email Address</label>
                <input id="forgotEmail" name="email" type="email" class="auth-input w-full pl-3 pr-3 py-3 rounded-xl" placeholder="you@example.com" required>
            </div>
            <button type="submit" class="btn-cy w-full mt-6">Continue</button>
            <p id="forgotMessage" class="text-sm text-center mt-4 text-c-25"></p>
        </form>
    </div>
</div>

<script>
function submitForgot(e){
    e.preventDefault();
    const email = document.getElementById('forgotEmail').value.trim();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/auth/password-reset/request', {
        method: 'POST',
        headers: { 'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN': token },
        body: JSON.stringify({ email })
    }).then(r=>r.json()).then(res=>{
        const msg = document.getElementById('forgotMessage');
        if(res.success){
            msg.textContent = 'We sent a verification code to your email if an account exists. Check your inbox.';
            setTimeout(()=> window.location.href = '/password/verify?email=' + encodeURIComponent(email), 1200);
        } else {
            msg.textContent = res.message || 'Unable to send verification code.';
        }
    }).catch(()=> document.getElementById('forgotMessage').textContent = 'Unable to send verification code.');
}
</script>
@endsection
