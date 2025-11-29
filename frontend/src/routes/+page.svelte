<script lang="ts">
  import { goto } from '$app/navigation';

  function scrollToPricing() {
    const el = document.getElementById('pricing');
    el?.scrollIntoView({ behavior: 'smooth' });
  }
</script>

<svelte:head>
  <title>Bezmidar Sitebuilder - Profesyonel Web Siteniz Hazır</title>
  <meta name="description" content="Domain, hosting ve özel tasarım tek pakette. Teknik bilgi gerektirmez." />
</svelte:head>

<!-- Hero Section -->
<section class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white py-20">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
    <h1 class="text-4xl md:text-6xl font-extrabold tracking-tight mb-6">
      Profesyonel Web Siteniz <br class="hidden md:block" />
      <span class="text-blue-200">Hazır ve Çalışır</span>
    </h1>
    <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto mb-10">
      Domain, hosting ve özel tasarım tek pakette. Teknik bilgi gerektirmez. Biz hallederiz.
    </p>
    <div class="flex justify-center space-x-4">
      <a href="/domain-check" class="bg-white text-blue-600 px-8 py-4 rounded-lg font-bold text-lg shadow-lg hover:bg-blue-50 transition duration-300">
        Hemen Başlayın →
      </a>
    </div>
  </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
      <div class="p-6 bg-gray-50 rounded-xl">
        <div class="text-4xl mb-4">🌐</div>
        <h3 class="text-xl font-bold mb-2">Domain</h3>
        <p class="text-gray-600">1 Yıllık .de veya .com domain dahil</p>
      </div>
      <div class="p-6 bg-gray-50 rounded-xl">
        <div class="text-4xl mb-4">🖥️</div>
        <h3 class="text-xl font-bold mb-2">Hosting</h3>
        <p class="text-gray-600">Hızlı SSD hosting ve SSL sertifikası</p>
      </div>
      <div class="p-6 bg-gray-50 rounded-xl">
        <div class="text-4xl mb-4">🎨</div>
        <h3 class="text-xl font-bold mb-2">Tasarım</h3>
        <p class="text-gray-600">Modern, mobil uyumlu profesyonel tasarım</p>
      </div>
      <div class="p-6 bg-gray-50 rounded-xl">
        <div class="text-4xl mb-4">🔧</div>
        <h3 class="text-xl font-bold mb-2">Bakım</h3>
        <p class="text-gray-600">7/24 teknik destek ve güncellemeler</p>
      </div>
    </div>
  </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="py-16 bg-gray-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold text-gray-900">Basit Fiyatlandırma</h2>
      <p class="mt-4 text-xl text-gray-600">Gizli ücret yok. Her şey dahil tek fiyat.</p>
    </div>

    <div class="max-w-lg mx-auto bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
      <div class="px-6 py-8 bg-blue-600 text-white text-center">
        <h3 class="text-2xl font-bold">Starter Paket</h3>
        <div class="mt-4 flex justify-center items-baseline">
          <span class="text-5xl font-extrabold">299€</span>
          <span class="ml-1 text-xl text-blue-100">/yıl</span>
        </div>
      </div>
      <div class="px-6 py-8">
        <ul class="space-y-4">
          <li class="flex items-center">
            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-gray-700">.de veya .com domain</span>
          </li>
          <li class="flex items-center">
            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-gray-700">Hızlı SSD hosting</span>
          </li>
          <li class="flex items-center">
            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-gray-700">SSL sertifikası (Güvenli Bağlantı)</span>
          </li>
          <li class="flex items-center">
            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-gray-700">Profesyonel Tasarım</span>
          </li>
          <li class="flex items-center">
            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-gray-700">Mobil Uyumlu</span>
          </li>
          <li class="flex items-center">
            <svg class="h-6 w-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-gray-700">1 Yıl Teknik Destek</span>
          </li>
        </ul>
        <div class="mt-8">
          <a href="/domain-check" class="block w-full bg-blue-600 text-white text-center px-6 py-4 rounded-lg font-bold hover:bg-blue-700 transition duration-300">
            Domain Seçin →
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- How it Works -->
<section class="py-16 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold text-gray-900">Nasıl Çalışır?</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
      <div class="relative">
        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
        <h3 class="text-xl font-bold mb-2">Domain Seçin</h3>
        <p class="text-gray-600">İşletmeniz için en uygun domain adını sorgulayın ve seçin.</p>
      </div>
      <div class="relative">
        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
        <h3 class="text-xl font-bold mb-2">Ödeme Yapın</h3>
        <p class="text-gray-600">Güvenli ödeme altyapımız ile paketinizi satın alın.</p>
      </div>
      <div class="relative">
        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
        <h3 class="text-xl font-bold mb-2">Siteniz Hazır</h3>
        <p class="text-gray-600">Bezmidar Sitebuilder sitenizi hazırlasın ve yayına alsın.</p>
      </div>
    </div>
  </div>
</section>
