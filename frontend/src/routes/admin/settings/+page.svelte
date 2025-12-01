<script lang="ts">
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import { adminAuth } from '$lib/stores/auth';

  let settings = {
    payment_url: ''
  };
  let loading = true;
  let saving = false;
  let message = '';

  onMount(async () => {
    try {
      const res = await fetch(`${API_URL}/admin/settings.php`, {
        headers: { 'Authorization': `Bearer ${$adminAuth.token}` }
      });
      const data = await res.json();
      if (data.success) {
        settings = { ...settings, ...data.settings };
      }
    } catch (e) {
      console.error(e);
    } finally {
      loading = false;
    }
  });

  async function saveSettings() {
    saving = true;
    message = '';
    try {
      const res = await fetch(`${API_URL}/admin/settings.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify(settings)
      });
      const data = await res.json();
      if (data.success) {
        message = 'Ayarlar kaydedildi.';
      } else {
        message = 'Hata: ' + (data.error || 'Bilinmeyen hata');
      }
    } catch (e) {
      message = 'Bağlantı hatası.';
    } finally {
      saving = false;
    }
  }
</script>

<svelte:head>
  <title>Ayarlar - Admin - Bezmidar Sitebuilder</title>
</svelte:head>

<h1 class="text-2xl font-bold text-gray-900 mb-8">Ayarlar</h1>

{#if loading}
  <div>Yükleniyor...</div>
{:else}
  <div class="bg-white shadow rounded-lg p-6 max-w-2xl">
    <div class="mb-6">
      <label for="payment_url" class="block text-sm font-medium text-gray-700 mb-2">Ruul.io Ödeme Linki</label>
      <p class="text-xs text-gray-500 mb-2">Sipariş numarası bu linkin sonuna otomatik olarak eklenecektir.</p>
      <input 
        type="text" 
        id="payment_url" 
        bind:value={settings.payment_url} 
        class="w-full p-3 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
      />
    </div>

    {#if message}
      <div class="mb-4 p-3 rounded {message.includes('Hata') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}">
        {message}
      </div>
    {/if}

    <button 
      on:click={saveSettings} 
      disabled={saving}
      class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-700 disabled:opacity-50"
    >
      {saving ? 'Kaydediliyor...' : 'Kaydet'}
    </button>
  </div>
{/if}
