<form method="POST" action="/register">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <label>Name</label>
    <input type="text" name="name" required>

    <label>Email</label>
    <input type="email" value="{{ $email }}" disabled>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Confirm Password</label>
    <input type="password" name="password_confirmation" required>

    <button type="submit">Register</button>
</form>
