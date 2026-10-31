@php
    $general_setting = App\Models\GeneralSettings::where('status', 1)->first();
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#05421c">
    <title>Owner Login | {{ $general_setting->title ?? 'Snapkid' }}</title>
    @if(!empty($general_setting->fav_icon))
        <link rel="icon" type="image/png" href="{{ $general_setting->fav_icon }}">
    @endif
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin_assets/plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        :root { --green: #05421c; --green-dark: #032b12; --yellow: #fcee21; --light: #8bc63e; --word: #36b54a; }
        * { box-sizing: border-box; }
        html, body { min-height: 100%; margin: 0; }
        body {
            font-family: 'Roboto', sans-serif; color: #1e293b;
            background: var(--green);
            display: flex; padding: 20px 16px; min-height: 100vh;
        }
        /* margin auto centres the box but lets it scroll when the screen is short (phone turned sideways) */
        .login { width: 100%; max-width: 380px; margin: auto; }
        .panel { background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 12px 32px rgba(0, 0, 0, .25); }
        .panel-top { background: #fff; text-align: center; padding: 26px 20px 8px; border-top: 5px solid var(--yellow); }
        .panel-top img { max-width: 150px; max-height: 90px; height: auto; }
        .panel-top h1 { font-size: 20px; font-weight: 700; color: var(--green); margin: 14px 0 2px; }
        .panel-top p { font-size: 13px; color: #64748b; margin: 0; }
        form { padding: 18px 22px 24px; }
        label { display: block; font-size: 11px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: var(--green); margin: 0 0 5px; }
        .field { position: relative; margin-bottom: 14px; }
        .field i.lead { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--green); font-size: 14px; }
        .field input {
            width: 100%; height: 46px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 0 44px 0 38px;
            font-size: 15px; font-family: inherit; color: #1e293b; background: #fff; outline: none;
        }
        .field input:focus { border-color: var(--green); box-shadow: 0 0 0 3px rgba(252, 238, 33, .6); }
        .eye { position: absolute; right: 4px; top: 50%; transform: translateY(-50%); background: none; border: 0; color: #64748b; width: 38px; height: 38px; cursor: pointer; font-size: 15px; }
        .eye:hover { color: var(--green); }
        .btn {
            width: 100%; height: 48px; border: 0; border-radius: 6px; background: var(--green); color: #fff;
            font-size: 16px; font-weight: 700; font-family: inherit; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 6px;
            border-bottom: 3px solid var(--yellow);
        }
        .btn:hover { background: var(--green-dark); }
        .btn:disabled { opacity: .7; cursor: wait; }
        .msg { border-radius: 6px; padding: 9px 12px; font-size: 13px; margin-bottom: 14px; display: flex; gap: 8px; align-items: flex-start; }
        .msg.err { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
        .msg.ok { background: #f1f8f3; border: 1px solid #c3e6cb; color: var(--green); }
        .foot { text-align: center; color: rgba(255, 255, 255, .75); font-size: 12px; margin-top: 14px; }
        .foot b { color: var(--yellow); }
    </style>
</head>

<body>
    <div class="login">
        <div class="panel">
            <div class="panel-top">
                @if(!empty($general_setting->logo))
                    <img src="{{ $general_setting->logo }}" alt="{{ $general_setting->title ?? 'Logo' }}">
                @endif
                <h1>Owner Login</h1>
                <p>Reports, stock, lots and ledgers</p>
            </div>

            <form method="POST" action="{{ route('owner.login.post') }}" onsubmit="this.querySelector('.btn').disabled = true;">
                @csrf
                @if(session('error'))
                    <div class="msg err"><i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span></div>
                @elseif($errors->any())
                    <div class="msg err"><i class="fas fa-exclamation-circle"></i><span>{{ $errors->first() }}</span></div>
                @elseif(session('success'))
                    <div class="msg ok"><i class="fas fa-check-circle"></i><span>{{ session('success') }}</span></div>
                @endif

                <label for="email">Email</label>
                <div class="field">
                    <i class="fas fa-user-tie lead"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Owner email" autocomplete="username" required autofocus>
                </div>

                <label for="password">Password</label>
                <div class="field">
                    <i class="fas fa-lock lead"></i>
                    <input type="password" id="password" name="password" placeholder="Password" autocomplete="current-password" required>
                    <button type="button" class="eye" title="Show password" onclick="var p = document.getElementById('password'); var show = p.type === 'password'; p.type = show ? 'text' : 'password'; this.firstElementChild.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';"><i class="fas fa-eye"></i></button>
                </div>

                <button type="submit" class="btn"><i class="fas fa-sign-in-alt"></i> Log in</button>
            </form>
        </div>
        <div class="foot"><b>{{ $general_setting->title ?? 'Snapkid' }}</b> &middot; Owner panel</div>
    </div>
</body>

</html>
