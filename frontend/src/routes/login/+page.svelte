<script lang="ts">
  import { goto } from '$app/navigation';
  import { customerAuth } from '$lib/stores/auth';
  import { API_URL } from '$lib/api';
  import { page } from '$app/stores';

  let email = '';
  let password = '';
  let error = '';
  let loading = false;

  async function handleLogin() {
    loading = true;
    error = '';

    try {
      const res = await fetch(`${API_URL}/auth/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password })
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

        // URL param overrides everything
        const urlRedirect = $page.url.searchParams.get('redirect');
        if (urlRedirect) {
            redirectUrl = urlRedirect;
        }

        goto(redirectUrl);
      } else {
        error = data.error || 'Giriş başarısız.';
      }
    } catch (e) {
      error = 'Bir hata oluştu. Lütfen tekrar deneyin.';
    } finally {
      loading = false;
    }
  }
</script>

<svelte:head>
  <title>Giriş Yap - Bezmidar Sitebuilder</title>
</svelte:head>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    <div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-brand-dark">
        Hesabınıza Giriş Yapın
      </h2>
      <p class="mt-2 text-center text-sm text-gray-600">
        Veya <a href="/register" class="font-medium text-brand-dark hover:text-opacity-80">yeni bir hesap oluşturun</a>
      </p>
    </div>
    <form class="mt-8 space-y-6" on:submit|preventDefault={handleLogin}>
      <div class="rounded-md shadow-sm -space-y-px">
        <div>
          <label for="login-email" class="sr-only">Email Adresi</label>
          <input id="login-email" name="email" type="email" autocomplete="email" required bind:value={email} class="appearance-none rounded-none rounded-t-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-light focus:border-brand-light focus:z-10 sm:text-sm" placeholder="Email Adresi">
        </div>
        <div>
          <label for="login-password" class="sr-only">Şifre</label>
          <input id="login-password" name="password" type="password" autocomplete="current-password" required bind:value={password} class="appearance-none rounded-none rounded-b-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-brand-light focus:border-brand-light focus:z-10 sm:text-sm" placeholder="Şifre">
        </div>
      </div>

      {#if error}
        <div class="text-red-500 text-sm text-center">{error}</div>
      {/if}

      <div>
        <button type="submit" disabled={loading} class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-brand-dark hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-dark disabled:opacity-50">
          {loading ? 'Giriş Yapılıyor...' : 'Giriş Yap'}
        </button>
      </div>
    </form>
  </div>
</div>
