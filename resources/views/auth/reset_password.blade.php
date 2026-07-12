@extends('welcome')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="auth-panel w-full max-w-md p-8">
        <h2 class="text-2xl font-black text-c text-center mb-4">Create New Password</h2>
        <p class="text-center text-c-25 mb-4">Set a new password for your account.</p>
        <form id="resetForm" onsubmit="submitReset(event)">
            <div>
                <label class="block text-xs font-bold text-c-40 uppercase mb-2">New Password</label>
                <input id="newPassword" name="password" type="password" class="auth-input w-full pl-3 pr-10 py-3 rounded-xl" required>
            </div>
            <div class="mt-4">
                <label class="block text-xs font-bold text-c-40 uppercase mb-2">Confirm Password</label>
                <input id="confirmPassword" name="password_confirmation" type="password" class="auth-input w-full pl-3 pr-3 py-3 rounded-xl" required>
            </div>
            <div id="pwStrength" class="text-xs text-c-25 mt-2"></div>
            <button type="submit" class="btn-cy w-full mt-6">Save Password</button>
            <p id="resetMessage" class="text-sm text-center mt-4 text-c-25"></p>
        </form>
    </div>
</div>

<script>
function strengthScore(pw){
    let score=0; if(pw.length>=8) score++; if(/[A-Z]/.test(pw)) score++; if(/[a-z]/.test(pw)) score++; if(/[0-9]/.test(pw)) score++; if(/[^A-Za-z0-9]/.test(pw)) score++; return score;
}

document.getElementById('newPassword').addEventListener('input', function(){
    const s = strengthScore(this.value); const el = document.getElementById('pwStrength');
    const labels=['Very weak','Weak','Fair','Good','Strong','Very strong']; el.textContent = 'Strength: '+labels[s];
});

function submitReset(e){
    e.preventDefault();
    const password = document.getElementById('newPassword').value.trim();
    const password_confirmation = document.getElementById('confirmPassword').value.trim();
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/auth/password-reset/complete', { method:'POST', headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token}, body: JSON.stringify({ password, password_confirmation }) })
    .then(r=>r.json()).then(res=>{ const msg = document.getElementById('resetMessage'); if(res.success){ msg.textContent = res.message; setTimeout(()=> window.location.href = '/login',1200); } else { msg.textContent = res.message || 'Unable to update password.'; } })
    .catch(()=> document.getElementById('resetMessage').textContent = 'Unable to update password.');
}
</script>

@endsection
