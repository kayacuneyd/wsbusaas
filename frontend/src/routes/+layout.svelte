<script lang="ts">
  import "../app.css";
  import Header from "$lib/components/Header.svelte";
  import Footer from "$lib/components/Footer.svelte";
</script>

<div class="flex flex-col min-h-screen bg-gray-50">
  <Header />
  <main class="flex-grow">
    <slot />
  </main>
  <Footer />
</div>
