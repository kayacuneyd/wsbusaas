<script lang="ts">
  import { goto } from '$app/navigation';
  import { customerAuth } from '$lib/stores/auth';
  import { API_URL } from '$lib/api';

  let fullName = '';
  let email = '';
  let password = '';
  let error = '';
  let loading = false;

  async function handleRegister() {
    loading = true;
    error = '';

    try {
      const res = await fetch(`${API_URL}/auth/register`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ full_name: fullName, email, password })
      });

      const data = await res.json();

      if (data.success) {
        customerAuth.set({
          isAuthenticated: true,
          token: data.token,
          user: data.user
        });
        
        // Check cart
        let redirectUrl = '/dashboard';
        const cartItems = localStorage.getItem('cart');
        if (cartItems) {
            redirectUrl = '/checkout';
        }

        goto(redirectUrl);
      } else {
        error = data.error || 'Kayıt başarısız.';
      }
    } catch (e) {
      error = 'Bir hata oluştu. Lütfen tekrar deneyin.';
    } finally {
      loading = false;
    }
  }
</script>

<svelte:head>
  <title>Kayıt Ol - Website Builder</title>
</svelte:head>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    <div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Yeni Hesap Oluşturun
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Veya <a href="/login" class="font-medium text-blue-600 hover:text-blue-500">mevcut hesabınıza giriş yapın</a>
      </p>
    </div>
    <form class="mt-8 space-y-6" on:submit|preventDefault={handleRegister}>
      <div class="rounded-md shadow-sm -space-y-px">
        <div>
          <label for="register-name" class="sr-only">Ad Soyad</label>
          <input id="register-name" name="full_name" type="text" required bind:value={fullName} class="appearance-none rounded-none rounded-t-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Ad Soyad">
        </div>
        <div>
          <label for="register-email" class="sr-only">Email Adresi</label>
          <input id="register-email" name="email" type="email" autocomplete="email" required bind:value={email} class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Email Adresi">
        </div>
        <div>
          <label for="register-password" class="sr-only">Şifre</label>
          <input id="register-password" name="password" type="password" autocomplete="new-password" required bind:value={password} class="appearance-none rounded-none rounded-b-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Şifre">
        </div>
      </div>

      {#if error}
        <div class="text-red-500 text-sm text-center">{error}</div>
      {/if}

      <div>
        <button type="submit" disabled={loading} class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
          {loading ? 'Kayıt Olunuyor...' : 'Kayıt Ol'}
        </button>
      </div>
    </form>
  </div>
</div>
