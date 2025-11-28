<script lang="ts">
  import { onMount } from 'svelte';
  import { API_URL } from '$lib/api';
  import { adminAuth } from '$lib/stores/auth';

  let stats = {
    pending: 0,
    completed: 0,
    revenue_mtd: 0
  };
  let loading = true;

  onMount(async () => {
    try {
      const res = await fetch(`${API_URL}/admin/stats`, {
        headers: {
          'Authorization': `Bearer ${$adminAuth.token}`
        }
      });
      const data = await res.json();
      if (data.success) {
        stats = data.stats;
      }
    } catch (e) {
      console.error(e);
    } finally {
      loading = false;
    }
  });
</script>

<svelte:head>
  <title>Admin Dashboard - Bezmidar Sitebuilder</title>
</svelte:head>

<h1 class="text-2xl font-bold text-gray-900 mb-8">Dashboard</h1>

<div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
  <div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <dt class="text-sm font-medium text-gray-500 truncate">Bekleyen Siparişler</dt>
      <dd class="mt-1 text-3xl font-semibold text-gray-900">{stats.pending}</dd>
    </div>
  </div>

  <div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <dt class="text-sm font-medium text-gray-500 truncate">Tamamlanan Siparişler</dt>
      <dd class="mt-1 text-3xl font-semibold text-gray-900">{stats.completed}</dd>
    </div>
  </div>

  <div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <dt class="text-sm font-medium text-gray-500 truncate">Bu Ay Ciro</dt>
      <dd class="mt-1 text-3xl font-semibold text-gray-900">{stats.revenue_mtd}€</dd>
    </div>
  </div>
</div>
