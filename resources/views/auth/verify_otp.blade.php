@extends('welcome')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="auth-panel w-full max-w-md p-8">
        <h2 class="text-2xl font-black text-c text-center mb-4">Verify Your Email</h2>
        <p class="text-center text-c-25 mb-4">We sent a 6-digit verification code to:</p>
        <p id="targetEmail" class="text-center font-mono mb-4"></p>
        <form id="verifyForm" onsubmit="submitVerify(event)">
            <div class="flex gap-2 justify-center mb-4">
                <input id="otpInput" name="otp" maxlength="6" class="auth-input w-12 text-center" required>
            </div>
            <button type="submit" class="btn-cy w-full">Verify Code</button>
            <div class="flex items-center justify-between mt-4">
                <button type="button" id="resendBtn" class="btn-g text-xs" onclick="resendCode()">Resend Code</button>
                <div id="countdown" class="text-xs text-c-25">Resend available in 60s</div>
            </div>
            <p id="verifyMessage" class="text-sm text-center mt-4 text-c-25"></p>
        </form>
    </div>
</div>

<script>
let cooldown = 60;
let timer = null;
function startCooldown(){
    document.getElementById('resendBtn').disabled = true;
    document.getElementById('countdown').textContent = 'Resend available in ' + cooldown + 's';
    timer = setInterval(()=>{
        cooldown -=1; if(cooldown<=0){ clearInterval(timer); document.getElementById('resendBtn').disabled=false; document.getElementById('countdown').textContent='Resend Code'; cooldown=60; } else { document.getElementById('countdown').textContent='Resend available in '+cooldown+'s'; } },1000);
}

window.addEventListener('DOMContentLoaded', ()=>{
    const params = new URLSearchParams(window.location.search);
    const email = params.get('email') || '';
    document.getElementById('targetEmail').textContent = email;
    startCooldown();
});

function submitVerify(e){
    e.preventDefault();
    const params = new URLSearchParams(window.location.search);
    const email = params.get('email') || document.getElementById('targetEmail').textContent;
    const otp = document.getElementById('otpInput').value.trim();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/auth/password-reset/verify', {
        method:'POST', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token},
        body: JSON.stringify({ email, otp })
    }).then(r=>r.json()).then(res=>{
        const msg = document.getElementById('verifyMessage');
        if(res.success){ msg.textContent = res.message; setTimeout(()=> window.location.href = '/password/reset',800); } else { msg.textContent = res.message || 'Verification failed.'; }
    }).catch(()=> document.getElementById('verifyMessage').textContent = 'Unable to verify code.');
}

function resendCode(){
    const params = new URLSearchParams(window.location.search);
    const email = params.get('email') || document.getElementById('targetEmail').textContent;
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/auth/password-reset/request', { method:'POST', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token}, body: JSON.stringify({ email }) })
    .then(r=>r.json()).then(res=>{ document.getElementById('verifyMessage').textContent = res.message || ''; startCooldown(); })
    .catch(()=> document.getElementById('verifyMessage').textContent = 'Unable to resend code.');
}
</script>

@endsection
