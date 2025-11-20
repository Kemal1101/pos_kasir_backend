<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Login - SuperCashier</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
	<style>
		body { font-family: 'Inter', sans-serif; }
	</style>
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top_left,_#6E56CF_0%,_#4F46E5_40%,_#402E8A_75%)] flex items-center justify-center p-4">
	<div class="w-full max-w-xl">
		<div class="bg-white rounded-[2rem] shadow-xl border border-gray-200 overflow-hidden">
			<div class="px-10 pt-8 pb-4 border-b">
				<h1 class="text-center text-lg font-semibold text-gray-800">Login</h1>
			</div>
			<div class="px-10 pt-8 pb-10">
				<div class="flex flex-col items-center mb-8">
					<!-- Logo -->
					<img src="{{ asset('Logo & APP.png') }}" alt="SuperCashier" class="h-28 mb-4 object-contain">
					<div class="text-center space-y-2">
						<p class="font-semibold text-gray-800">Selamat Datang Kembali !</p>
						<p class="text-sm text-gray-600">Untuk saat ini Anda hanya dapat masuk sebagai Owner</p>
					</div>
				</div>

				@if(session('error'))
					<div class="mb-5 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2">
						{{ session('error') }}
					</div>
				@endif
				@if(session('success'))
					<div class="mb-5 text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg px-4 py-2">
						{{ session('success') }}
					</div>
				@endif

				<form method="POST" action="{{ route('login.perform') }}" class="space-y-6">
					@csrf
					<div class="space-y-1">
						<label class="text-sm font-semibold text-gray-700">Email</label>
						<input type="email" name="email" value="{{ old('email') }}" required autofocus
							   class="w-full px-4 py-3 rounded-lg bg-gray-200 border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 placeholder-gray-500 text-sm"
							   placeholder="contoh: supercashier@gmail.com" />
					</div>
					<div class="space-y-1">
						<label class="text-sm font-semibold text-gray-700">Password</label>
						<input type="password" name="password" required
							   class="w-full px-4 py-3 rounded-lg bg-gray-200 border border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 placeholder-gray-500 text-sm"
							   placeholder="Password" />
						<div class="flex justify-end mt-1">
							<a href="#" class="text-[11px] font-medium text-purple-600 hover:text-purple-500">Lupa Password?</a>
						</div>
					</div>
					<button class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-fuchsia-600 hover:brightness-110 text-white font-semibold py-3 text-sm shadow-md transition">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/></svg>
						Login
					</button>
				</form>
			</div>
		</div>
		<p class="text-center text-xs text-gray-300 mt-6">&copy; {{ date('Y') }} SuperCashier. All rights reserved.</p>
	</div>
</body>
</html>
