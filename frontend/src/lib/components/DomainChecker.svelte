<script lang="ts">
  import { checkDomain } from '$lib/api';
  import { goto } from '$app/navigation';

  let domain = '';
  let tld = 'de';
  let loading = false;
  let result: any = null;
  let error = '';

  async function handleCheck() {
    if (!domain) return;
    loading = true;
    error = '';
    result = null;

    try {
      const res = await checkDomain(domain, tld);
      if (res.success) {
        result = res;
      } else {
        error = res.message || 'Bir hata oluştu.';
      }
    } catch (e) {
      error = 'Bağlantı hatası.';
    } finally {
      loading = false;
    }
  }

  function handleContinue() {
    if (result && result.available) {
      // Navigate to checkout with domain param
      goto(`/checkout?domain=${result.domain}`);
    }
  }
</script>

<div class="bg-white p-8 rounded-2xl shadow-xl max-w-2xl mx-auto">
  <h2 class="text-2xl font-bold text-center mb-6">Hayalinizdeki Domain'i Kontrol Edin</h2>
  
  <div class="flex flex-col md:flex-row gap-4 mb-6">
    <label for="domain-input" class="sr-only">Domain Adı</label>
    <input 
      type="text" 
      id="domain-input"
      bind:value={domain} 
      placeholder="meingeschaeft" 
      class="flex-grow p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
      on:keydown={(e) => e.key === 'Enter' && handleCheck()}
    />
    <label for="tld-select" class="sr-only">Uzantı</label>
    <select 
      id="tld-select"
      bind:value={tld} 
      class="p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white"
    >
      <option value="de">.de</option>
      <option value="com">.com</option>
      <option value="eu">.eu</option>
      <option value="net">.net</option>
      <option value="org">.org</option>
    </select>
    <button 
      on:click={handleCheck} 
      disabled={loading || !domain}
      class="bg-blue-600 text-white px-8 py-4 rounded-lg font-bold hover:bg-blue-700 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      {loading ? 'Kontrol ediliyor...' : 'Kontrol Et'}
    </button>
  </div>

  {#if result}
    <div class="mt-6 p-6 rounded-xl {result.available ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}">
      {#if result.available}
        <div class="text-center">
          <div class="text-green-600 text-xl font-bold mb-2">✅ {result.domain} MÜSAİT!</div>
          <p class="text-green-700 mb-6">Bu domain sizin olabilir.</p>
          <button 
            on:click={handleContinue}
            class="bg-green-600 text-white px-8 py-3 rounded-lg font-bold hover:bg-green-700 transition duration-300"
          >
            Bu Domain ile Devam Et →
          </button>
        </div>
      {:else}
        <div class="text-center">
          <div class="text-red-600 text-xl font-bold mb-2">❌ {result.domain} ALINMIŞ</div>
          <p class="text-red-700">Lütfen başka bir domain deneyin.</p>
          {#if result.suggestions && result.suggestions.length > 0}
            <div class="mt-4 text-left">
              <p class="font-bold text-gray-700 mb-2">Alternatif öneriler:</p>
              <ul class="space-y-2">
                {#each result.suggestions as suggestion}
                  <li class="flex justify-between items-center bg-white p-3 rounded border border-gray-200">
                    <span>{suggestion}</span>
                    <button class="text-blue-600 font-medium text-sm hover:underline">Seç</button>
                  </li>
                {/each}
              </ul>
            </div>
          {/if}
        </div>
      {/if}
    </div>
  {/if}

  {#if error}
    <div class="mt-6 p-4 bg-red-100 text-red-700 rounded-lg text-center">
      {error}
    </div>
  {/if}
</div>
