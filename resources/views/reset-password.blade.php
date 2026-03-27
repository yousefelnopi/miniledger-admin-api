<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            text-align: center;
            box-sizing: border-box;
        }
        h2 {
            color: #1f2937;
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 24px;
        }
        p.subtitle {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        input[type="password"] {
            width: 100%;
            padding: 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 15px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        input[type="password"]:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }
        button {
            background: #4F46E5;
            color: white;
            border: none;
            padding: 14px 20px;
            cursor: pointer;
            border-radius: 8px;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            transition: background 0.3s, transform 0.1s;
        }
        button:hover {
            background: #4338ca;
        }
        button:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        #message {
            margin-top: 25px;
            font-weight: 500;
            font-size: 15px;
            border-radius: 8px;
            display: none; /* مخفي في البداية */
        }
        /* تصميم رسائل النجاح والخطأ */
        .success-msg { color: #065f46; background: #d1fae5; padding: 15px; border: 1px solid #a7f3d0; display: block !important;}
        .error-msg { color: #991b1b; background: #fee2e2; padding: 15px; border: 1px solid #fecaca; display: block !important;}
        .processing-msg { color: #1e40af; background: #dbeafe; padding: 15px; border: 1px solid #bfdbfe; display: block !important;}
    </style>
</head>
<body>

<div class="container">
    <h2>Create New Password</h2>
    <p class="subtitle" id="subtitle">Please enter your new password below to regain access to your account.</p>

    <form id="resetForm">
        <input type="hidden" id="token" value="{{ $token }}">
        <input type="hidden" id="email" value="{{ $email }}">

        <div class="input-group">
            <input type="password" id="password" placeholder="New Password" required minlength="8">
        </div>

        <div class="input-group">
            <input type="password" id="password_confirmation" placeholder="Confirm New Password" required minlength="8">
        </div>

        <button type="submit" id="submitBtn">Update Password</button>
    </form>

    <div id="message"></div>
</div>

<script>
    document.getElementById('resetForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const messageDiv = document.getElementById('message');
        const resetForm = document.getElementById('resetForm');
        const subtitle = document.getElementById('subtitle');
        const submitBtn = document.getElementById('submitBtn');

        // تغيير شكل الزرار والرسالة وقت التحميل
        messageDiv.className = 'processing-msg';
        messageDiv.innerText = 'Processing your request...';
        submitBtn.disabled = true;
        submitBtn.innerText = 'Please wait...';

        const data = {
            email: document.getElementById('email').value,
            token: document.getElementById('token').value,
            password: document.getElementById('password').value,
            password_confirmation: document.getElementById('password_confirmation').value
        };

        fetch('/api/reset-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === 'Password reset successfully. You can now login.') {
                // الفركش: نجاح العملية
                messageDiv.className = 'success-msg';
                messageDiv.innerText = '✅ ' + data.message;

                // نخفي الفورم والنص اللي فوقها
                resetForm.style.display = 'none';
                subtitle.style.display = 'none';
            } else {
                // خطأ من الباك إند (مثلا الباسورد مش متطابق أو اللينك منتهي)
                messageDiv.className = 'error-msg';
                messageDiv.innerText = '❌ ' + (data.message || 'An error occurred!');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Update Password';
            }
        })
        .catch(error => {
            // خطأ في الشبكة
            messageDiv.className = 'error-msg';
            messageDiv.innerText = '❌ Network Error! Please try again.';
            submitBtn.disabled = false;
            submitBtn.innerText = 'Update Password';
        });
    });
</script>

</body>
</html>
