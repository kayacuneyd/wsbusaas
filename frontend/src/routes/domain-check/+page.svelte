<script lang="ts">
  import DomainChecker from '$lib/components/DomainChecker.svelte';
</script>

<svelte:head>
  <title>Domain Sorgulama - WebsiteBuilder</title>
</svelte:head>

<div class="py-20 bg-gray-50 min-h-[80vh] flex items-center">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
    <div class="text-center mb-12">
      <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Domain Adınızı Seçin</h1>
      <p class="text-xl text-gray-600">İşletmeniz için en uygun ismi bulun.</p>
    </div>
    
    <DomainChecker />
  </div>
</div>
