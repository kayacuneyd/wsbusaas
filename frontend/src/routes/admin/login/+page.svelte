<script lang="ts">
  import { login } from '$lib/stores/auth';
  import { goto } from '$app/navigation';
  import { API_URL } from '$lib/api';

  let username = '';
  let password = '';
  let error = '';
  let loading = false;

  async function handleLogin() {
    loading = true;
    error = '';

    try {
      const res = await fetch(`${API_URL}/admin/login`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password })
      });
      const data = await res.json();

      if (data.token) {
        login(data.token);
        goto('/admin');
      } else {
        error = 'Giriş başarısız.';
      }
    } catch (e) {
      error = 'Bağlantı hatası.';
    } finally {
      loading = false;
    }
  }
</script>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-md w-full space-y-8">
    <div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">Admin Girişi</h2>
    </div>
    <form class="mt-8 space-y-6" on:submit|preventDefault={handleLogin}>
      <div class="rounded-md shadow-sm -space-y-px">
        <div>
          <label for="username" class="sr-only">Kullanıcı Adı</label>
          <input id="username" name="username" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Kullanıcı Adı" bind:value={username}>
        </div>
        <div>
          <label for="password" class="sr-only">Şifre</label>
          <input id="password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Şifre" bind:value={password}>
        </div>
      </div>

      {#if error}
        <div class="text-red-600 text-sm text-center">{error}</div>
      {/if}

      <div>
        <button type="submit" disabled={loading} class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
          {loading ? 'Giriş yapılıyor...' : 'Giriş Yap'}
        </button>
      </div>
    </form>
  </div>
</div>
