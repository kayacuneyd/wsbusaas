<script lang="ts">
  import { goto } from '$app/navigation';
  const themes = [
    { id: 'theme1', name: 'Theme 1', desc: 'Clean landing' },
    { id: 'theme2', name: 'Theme 2', desc: 'Sidebar layout' },
    { id: 'theme3', name: 'Theme 3', desc: 'Gradient hero' }
  ];
  const selectTheme = (id: string) => {
    localStorage.setItem('selectedTheme', id);
    goto('/customize');
  };
</script>

<section class="grid">
  {#each themes as t}
    <article class="card" on:click={() => selectTheme(t.id)}>
      <h3>{t.name}</h3>
      <p>{t.desc}</p>
      <button>Seç</button>
    </article>
  {/each}
</section>

<style>
  .grid { max-width: 900px; margin: 60px auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px,1fr)); gap: 16px; }
  .card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; cursor: pointer; }
  button { margin-top: 12px; padding: 10px 14px; background: #22d3ee; border: none; border-radius: 8px; }
</style>
