<script lang="ts">
  import { customerAuth } from '$lib/stores/auth';
</script>

<header class="bg-white shadow-sm sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-16">
      <div class="flex items-center">
        <div class="flex-shrink-0 flex items-center">
          <a href="/" class="text-2xl font-bold text-blue-600">WebsiteBuilder</a>
        </div>
        <!-- Main navigation links -->
        <nav class="hidden md:flex space-x-8 ml-10">
          <a href="/" class="text-gray-600 hover:text-blue-600">Ana Sayfa</a>
          <a href="/#pricing" class="text-gray-600 hover:text-blue-600">Paketler</a>
        </nav>
      </div>
      <div class="flex items-center space-x-4">
        {#if $customerAuth.isAuthenticated}
          <a href="/dashboard" class="text-gray-600 hover:text-blue-600 font-medium">Hesabım</a>
          <button on:click={() => {
            customerAuth.set({ isAuthenticated: false, token: null, user: null });
            window.location.href = '/';
          }} class="text-red-600 hover:text-red-700">Çıkış</button>
        {:else}
          <a href="/login" class="text-gray-600 hover:text-blue-600">Giriş Yap</a>
          <a href="/register" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Kayıt Ol</a>
        {/if}
        <a href="/admin/login" class="text-gray-400 hover:text-gray-500 text-sm">Admin</a>
      </div>
    </div>
  </div>
</header>
