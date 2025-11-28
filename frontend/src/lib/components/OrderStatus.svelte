<script lang="ts">
  import {
    DEFAULT_ORDER_STATE,
    ORDER_STATES,
    type OrderStateMeta,
    getOrderProgressPercent,
    getOrderState,
    getOrderStateIndex
  } from '$lib/constants/orderStatus';

  type StatusHistoryEntry = {
    status: string;
    note?: string | null;
    changed_by?: string | null;
    created_at?: string;
  };

  export let order: any;

  const fallbackState = DEFAULT_ORDER_STATE;

  $: statusKey = order?.order_status ?? order?.status ?? fallbackState.key;
  $: currentState: OrderStateMeta = getOrderState(statusKey);
  $: progressPercent = getOrderProgressPercent(statusKey);
  $: currentStepIndex = getOrderStateIndex(statusKey);
  let statusHistory: StatusHistoryEntry[] = [];
  let historyByStatus: Record<string, StatusHistoryEntry> = {};
  let orderedHistory: StatusHistoryEntry[] = [];
  let timelineSteps: { step: OrderStateMeta; index: number; entry?: StatusHistoryEntry }[] = [];

  $: statusHistory = Array.isArray(order?.status_history) ? order.status_history : [];
  $: historyByStatus = statusHistory.reduce<Record<string, StatusHistoryEntry>>((acc, entry) => {
    acc[entry.status] = entry;
    return acc;
  }, {});
  $: orderedHistory = [...statusHistory].sort((a, b) => {
    const aDate = a?.created_at ? new Date(a.created_at).getTime() : 0;
    const bDate = b?.created_at ? new Date(b.created_at).getTime() : 0;
    return bDate - aDate;
  });
  $: timelineSteps = ORDER_STATES.map((step, index) => ({
    step,
    index,
    entry: historyByStatus[step.key]
  }));

  function formatDate(value?: string | null) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('tr-TR');
  }
</script>

<div class="space-y-8 rounded-lg bg-white p-6 shadow-lg">
  <div class="flex flex-col gap-1">
    <h2 class="text-2xl font-bold text-gray-900">Sipariş Durumu</h2>
    <p class="text-sm text-gray-500">
      Son Güncelleme: {formatDate(order?.status_updated_at ?? order?.updated_at ?? order?.created_at)}
    </p>
  </div>

  <!-- Progress Bar with Icons -->
  <div class="relative">
    <div class="mb-6 h-4 overflow-hidden rounded-full bg-gray-200 shadow-inner">
      <div
        class="h-full rounded-full bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 transition-all duration-700 ease-out shadow-lg"
        style={`width: ${progressPercent}%`}
      ></div>
    </div>
    <div class="flex justify-between text-xs font-medium text-gray-600 md:text-sm">
      {#each ORDER_STATES as step, index}
        <div class="flex flex-col items-center">
          <div class={`flex items-center gap-1 ${index <= currentStepIndex ? 'text-blue-600 font-semibold' : 'text-gray-400'}`}>
            {#if index < currentStepIndex}
              <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
              </svg>
            {:else if index === currentStepIndex}
              <svg class="w-4 h-4 text-blue-600 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
              </svg>
            {:else}
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
              </svg>
            {/if}
            <span>{step.label}</span>
          </div>
        </div>
      {/each}
    </div>
  </div>

  <div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-4 rounded-xl border-2 border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 p-6 shadow-md">
      <div class="flex items-center gap-2">
        <div class="rounded-full bg-blue-100 p-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Aktif Durum</p>
      </div>
      <p class="text-3xl font-bold text-blue-900">{currentState.label}</p>
      <div class="space-y-2">
        <p class="text-base font-medium text-gray-800">{currentState.messages.tr}</p>
        <p class="text-sm italic text-gray-600">{currentState.messages.en}</p>
      </div>
      {#if order?.status_message}
        <div class="mt-4 rounded-lg border border-blue-200 bg-white/90 p-4 text-sm text-gray-700 shadow-sm">
          <p class="font-semibold text-blue-900 mb-2">📝 Son Not</p>
          <p class="mt-1 leading-relaxed">{order.status_message}</p>
          <p class="mt-3 text-xs text-blue-700 border-t border-blue-100 pt-2">
            Güncelleyen: {order.status_updated_by ?? 'sistem'} · {formatDate(order?.status_updated_at)}
          </p>
        </div>
      {/if}
    </div>

    <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-gray-50 to-white p-6 text-sm text-gray-700 shadow-md">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Sipariş Detayları</h3>
      <dl class="space-y-4">
        <div class="flex items-start justify-between border-b border-gray-100 pb-3">
          <dt class="text-gray-600 font-medium">Sipariş No</dt>
          <dd class="font-bold text-gray-900 text-right">{order?.order_id}</dd>
        </div>
        <div class="flex items-start justify-between border-b border-gray-100 pb-3">
          <dt class="text-gray-600 font-medium">Domain</dt>
          <dd class="font-semibold text-gray-900 break-all text-right">{order?.domain_name}</dd>
        </div>
        <div class="flex items-start justify-between border-b border-gray-100 pb-3">
          <dt class="text-gray-600 font-medium">Paket</dt>
          <dd class="font-semibold capitalize text-gray-900 text-right">{order?.package_type}</dd>
        </div>
        <div class="flex items-start justify-between pb-3">
          <dt class="text-gray-600 font-medium">Müşteri</dt>
          <dd class="font-semibold text-gray-900 text-right">
            <div>{order?.customer_name || '—'}</div>
            <div class="text-xs font-normal text-gray-500 mt-1">{order?.customer_email}</div>
          </dd>
        </div>
      </dl>
      <div class="mt-6 pt-4 border-t border-gray-200">
        <a
          class="inline-flex w-full items-center justify-center gap-2 rounded-lg border-2 border-blue-300 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 transition-all hover:bg-blue-600 hover:text-white hover:shadow-md"
          href="mailto:{order?.customer_email}"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          Destek ile İletişime Geç
        </a>
      </div>
    </div>
  </div>

  <div class="rounded-xl border border-gray-200 bg-gray-50 p-6">
    <h3 class="mb-6 text-xl font-bold text-gray-900 flex items-center gap-2">
      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
      </svg>
      Adım Adım İlerleme
    </h3>
    <div class="space-y-6">
      {#each timelineSteps as { step, index, entry } }
        <div class="flex gap-4">
          <div class="flex flex-col items-center">
            <div
              class={`h-10 w-10 rounded-full border-4 flex items-center justify-center shadow-md transition-all ${
                index < currentStepIndex 
                  ? 'border-green-500 bg-green-500 text-white' 
                  : index === currentStepIndex
                  ? 'border-blue-600 bg-blue-600 text-white animate-pulse'
                  : 'border-gray-300 bg-white text-gray-400'
              }`}
            >
              {#if index < currentStepIndex}
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
              {:else if index === currentStepIndex}
                <span class="text-sm font-bold">{index + 1}</span>
              {:else}
                <span class="text-sm font-bold">{index + 1}</span>
              {/if}
            </div>
            {#if index < ORDER_STATES.length - 1}
              <div
                class={`mt-2 w-1 flex-1 rounded-full transition-all ${
                  index < currentStepIndex ? 'bg-green-400' : 'bg-gray-200'
                }`}
                style="min-height: 40px;"
              ></div>
            {/if}
          </div>
          <div class="flex-1 pb-6">
            <div class={`rounded-lg p-4 transition-all ${
              index < currentStepIndex 
                ? 'bg-green-50 border border-green-200' 
                : index === currentStepIndex
                ? 'bg-blue-50 border-2 border-blue-300 shadow-md'
                : 'bg-white border border-gray-200'
            }`}>
              <p class={`font-bold text-lg mb-1 ${
                index <= currentStepIndex ? 'text-gray-900' : 'text-gray-500'
              }`}>{step.label}</p>
              <p class={`text-sm mb-2 ${
                index <= currentStepIndex ? 'text-gray-700' : 'text-gray-400'
              }`}>{step.messages.tr}</p>
              {#if entry}
                <div class="mt-3 pt-3 border-t border-gray-200">
                  <p class="text-xs text-gray-500 mb-1">
                    ✓ {formatDate(entry.created_at)} · {entry.changed_by ?? 'sistem'}
                  </p>
                  {#if entry.note}
                    <p class="mt-2 text-sm text-gray-700 bg-white/80 p-2 rounded border border-gray-100">{entry.note}</p>
                  {/if}
                </div>
              {:else}
                <p class="mt-2 text-xs text-gray-400 italic">⏳ Bu adım için güncelleme bekleniyor...</p>
              {/if}
            </div>
          </div>
        </div>
      {/each}
    </div>
  </div>

  {#if orderedHistory.length > 1}
    <div>
      <h3 class="mb-3 text-lg font-semibold text-gray-900">Durum Güncellemeleri</h3>
      <ul class="divide-y divide-gray-100 overflow-hidden rounded-lg border border-gray-100">
        {#each orderedHistory as entry}
          <li class="space-y-1 bg-white/90 p-4 text-sm">
            <p class="font-medium text-gray-900">{getOrderState(entry.status).label}</p>
            <p class="text-xs text-gray-500">{formatDate(entry.created_at)} · {entry.changed_by ?? 'sistem'}</p>
            {#if entry.note}
              <p class="text-gray-700">{entry.note}</p>
            {/if}
          </li>
        {/each}
      </ul>
    </div>
  {/if}
</div>
