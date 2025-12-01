<script lang="ts">
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import { adminAuth } from '$lib/stores/auth';

  let loading = true;
  let saving = false;
  let success = false;
  let settings = {
    seo_title: '',
    seo_description: '',
    seo_keywords: '',
    seo_og_image: '',
    seo_og_title: '',
    seo_og_description: ''
  };

  onMount(async () => {
    await loadSettings();
  });

  async function loadSettings() {
    try {
      const res = await fetch(`${API_URL}/admin/seo.php`, {
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
  }

  async function saveSettings() {
    saving = true;
    success = false;

    try {
      const res = await fetch(`${API_URL}/admin/seo.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${$adminAuth.token}`
        },
        body: JSON.stringify(settings)
      });

      const data = await res.json();
      if (data.success) {
        success = true;
        setTimeout(() => { success = false; }, 3000);
      } else {
        alert(data.error || 'Ayarlar kaydedilemedi.');
      }
    } catch (e) {
      alert('Bir hata oluştu.');
    } finally {
      saving = false;
    }
  }
</script>

<svelte:head>
  <title>SEO Ayarları - Admin - Bezmidar Sitebuilder</title>
</svelte:head>

<div class="max-w-4xl">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-900">SEO Ayarları</h1>
  </div>

  {#if loading}
    <div class="text-center py-12">Yükleniyor...</div>
  {:else}
    {#if success}
      <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
        <p class="text-green-800 font-medium">Ayarlar başarıyla kaydedildi!</p>
      </div>
    {/if}

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
      <form on:submit|preventDefault={saveSettings} class="space-y-6 p-6">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Site Başlığı (Title)</label>
          <input
            type="text"
            bind:value={settings.seo_title}
            placeholder="Bezmidar Sitebuilder - Profesyonel Web Siteniz Hazır"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
          <p class="mt-1 text-xs text-gray-500">Arama motorlarında görünecek ana başlık (50-60 karakter önerilir)</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Meta Açıklama (Description)</label>
          <textarea
            bind:value={settings.seo_description}
            rows="3"
            placeholder="Domain, hosting ve özel tasarım tek pakette. Teknik bilgi gerektirmez."
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          ></textarea>
          <p class="mt-1 text-xs text-gray-500">Arama sonuçlarında görünecek açıklama (150-160 karakter önerilir)</p>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Anahtar Kelimeler (Keywords)</label>
          <input
            type="text"
            bind:value={settings.seo_keywords}
            placeholder="website, domain, hosting, sitebuilder, bezmidar"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          />
          <p class="mt-1 text-xs text-gray-500">Virgülle ayrılmış anahtar kelimeler</p>
        </div>

        <div class="border-t border-gray-200 pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Open Graph (Sosyal Medya) Ayarları</h3>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">OG Başlık</label>
              <input
                type="text"
                bind:value={settings.seo_og_title}
                placeholder="Bezmidar Sitebuilder"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">OG Açıklama</label>
              <textarea
                bind:value={settings.seo_og_description}
                rows="2"
                placeholder="Profesyonel web sitenizi kolayca oluşturun"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">OG Görsel URL</label>
              <input
                type="url"
                bind:value={settings.seo_og_image}
                placeholder="https://bezmidar.de/images/og-image.jpg"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <p class="mt-1 text-xs text-gray-500">Sosyal medyada paylaşımda görünecek görsel (1200x630px önerilir)</p>
            </div>
          </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-gray-200">
          <button
            type="submit"
            disabled={saving}
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50"
          >
            {saving ? 'Kaydediliyor...' : 'Ayarları Kaydet'}
          </button>
        </div>
      </form>
    </div>
  {/if}
</div>

