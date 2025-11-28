<script lang="ts">
  import "../app.css";
  import Header from "$lib/components/Header.svelte";
  import Footer from "$lib/components/Footer.svelte";
  import { onMount } from 'svelte';
  import { seoSettings, loadSEOSettings } from '$lib/stores/seo';

  onMount(async () => {
    await loadSEOSettings();
  });
</script>

<svelte:head>
  <title>{$seoSettings.seo_title || 'Bezmidar Sitebuilder'}</title>
  <meta name="description" content={$seoSettings.seo_description || 'Domain, hosting ve özel tasarım tek pakette.'} />
  <meta name="keywords" content={$seoSettings.seo_keywords || 'website, domain, hosting, sitebuilder'} />
  
  {#if $seoSettings.seo_og_title || $seoSettings.seo_og_description}
    <meta property="og:title" content={$seoSettings.seo_og_title || $seoSettings.seo_title} />
    <meta property="og:description" content={$seoSettings.seo_og_description || $seoSettings.seo_description} />
    {#if $seoSettings.seo_og_image}
      <meta property="og:image" content={$seoSettings.seo_og_image} />
    {/if}
    <meta property="og:type" content="website" />
  {/if}
</svelte:head>

<div class="flex flex-col min-h-screen bg-gray-50">
  <Header />
  <main class="flex-grow">
    <slot />
  </main>
  <Footer />
</div>
