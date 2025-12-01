<script lang="ts">
  import { API_URL } from '$lib/api';

  let name = '';
  let email = '';
  let subject = '';
  let message = '';
  let loading = false;
  let success = false;
  let error = '';

  async function handleSubmit() {
    loading = true;
    error = '';
    success = false;

    try {
      const res = await fetch(`${API_URL}/contact.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, subject, message })
      });

      const data = await res.json();
      if (data.success) {
        success = true;
        name = '';
        email = '';
        subject = '';
        message = '';
      } else {
        error = data.error || 'Mesaj gönderilemedi.';
      }
    } catch (e) {
      error = 'Bir hata oluştu. Lütfen tekrar deneyin.';
    } finally {
      loading = false;
    }
  }
</script>

<svelte:head>
  <title>İletişim - Bezmidar Sitebuilder</title>
</svelte:head>

<div class="py-12 bg-gray-50 min-h-screen">
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
      <div class="px-6 py-8 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
        <h1 class="text-3xl font-bold">İletişime Geçin</h1>
        <p class="mt-2 text-blue-100">Sorularınız, önerileriniz veya destek talepleriniz için bize ulaşın.</p>
      </div>

      <div class="px-6 py-8">
        {#if success}
          <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <p class="text-green-800 font-medium">Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.</p>
            </div>
          </div>
        {/if}

        {#if error}
          <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-red-800">{error}</p>
          </div>
        {/if}

        <form on:submit|preventDefault={handleSubmit} class="space-y-6">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Ad Soyad *</label>
            <input
              id="name"
              type="text"
              bind:value={name}
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
            />
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">E-posta Adresi *</label>
            <input
              id="email"
              type="email"
              bind:value={email}
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
            />
          </div>

          <div>
            <label for="subject" class="block text-sm font-medium text-gray-700">Konu</label>
            <input
              id="subject"
              type="text"
              bind:value={subject}
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
            />
          </div>

          <div>
            <label for="message" class="block text-sm font-medium text-gray-700">Mesaj *</label>
            <textarea
              id="message"
              bind:value={message}
              required
              rows="6"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-2"
            ></textarea>
          </div>

          <div>
            <button
              type="submit"
              disabled={loading}
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              {loading ? 'Gönderiliyor...' : 'Mesaj Gönder'}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

