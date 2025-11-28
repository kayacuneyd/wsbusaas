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

<div class="space-y-8 rounded-lg bg-white p-6 shadow">
  <div class="flex flex-col gap-1">
    <h2 class="text-xl font-bold text-gray-900">Sipariş Durumu</h2>
    <p class="text-sm text-gray-500">
      Güncellenme: {formatDate(order?.status_updated_at ?? order?.updated_at ?? order?.created_at)}
    </p>
  </div>

  <div>
    <div class="mb-4 h-3 overflow-hidden rounded-full bg-gray-100">
      <div
        class="h-full rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500"
        style={`width: ${progressPercent}%`}
      ></div>
    </div>
    <div class="flex justify-between text-xs font-medium text-gray-500 md:text-sm">
      {#each ORDER_STATES as step, index}
        <span class={index <= currentStepIndex ? 'text-blue-600' : ''}>{step.label}</span>
      {/each}
    </div>
  </div>

  <div class="grid gap-6 md:grid-cols-2">
    <div class="space-y-3 rounded-lg border border-blue-100 bg-blue-50/60 p-4">
      <p class="text-xs font-semibold uppercase tracking-wide text-blue-500">Aktif Durum</p>
      <p class="text-2xl font-bold text-blue-700">{currentState.label}</p>
      <p class="text-gray-800">{currentState.messages.tr}</p>
      <p class="text-sm italic text-gray-600">{currentState.messages.en}</p>
      {#if order?.status_message}
        <div class="mt-4 rounded-lg border border-blue-200 bg-white/80 p-3 text-sm text-gray-700">
          <p class="font-medium text-blue-900">Son Not</p>
          <p class="mt-1">{order.status_message}</p>
          <p class="mt-2 text-xs text-blue-700">
            Güncelleyen: {order.status_updated_by ?? 'sistem'} · {formatDate(order?.status_updated_at)}
          </p>
        </div>
      {/if}
    </div>

    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 text-sm text-gray-700">
      <dl class="space-y-4">
        <div>
          <dt class="text-gray-500">Sipariş No</dt>
          <dd class="font-semibold text-gray-900">{order?.order_id}</dd>
        </div>
        <div>
          <dt class="text-gray-500">Domain</dt>
          <dd class="font-semibold text-gray-900 break-all">{order?.domain_name}</dd>
        </div>
        <div>
          <dt class="text-gray-500">Paket</dt>
          <dd class="font-semibold capitalize text-gray-900">{order?.package_type}</dd>
        </div>
        <div>
          <dt class="text-gray-500">Müşteri</dt>
          <dd class="font-semibold text-gray-900">
            {order?.customer_name}
            <span class="block text-xs font-normal text-gray-500">{order?.customer_email}</span>
          </dd>
        </div>
      </dl>
      <div class="mt-6">
        <a
          class="inline-flex w-full items-center justify-center rounded-md border border-blue-200 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-600 hover:text-white"
          href="mailto:{order?.customer_email}"
        >
          Destek ile İletişime Geç
        </a>
      </div>
    </div>
  </div>

  <div>
    <h3 class="mb-4 text-lg font-semibold text-gray-900">Adım Adım İlerleme</h3>
    <div class="space-y-6">
      {#each timelineSteps as { step, index, entry } }
        <div class="flex gap-4">
          <div class="flex flex-col items-center">
            <div
              class={`h-4 w-4 rounded-full border-2 ${
                index <= currentStepIndex ? 'border-blue-600 bg-blue-600' : 'border-gray-300 bg-white'
              }`}
            ></div>
            {#if index < ORDER_STATES.length - 1}
              <div
                class={`mt-1 w-px flex-1 ${
                  index < currentStepIndex ? 'bg-blue-500' : 'bg-gray-200'
                }`}
              ></div>
            {/if}
          </div>
          <div class="flex-1">
            <p class="font-semibold text-gray-900">{step.label}</p>
            <p class="text-sm text-gray-600">{step.messages.tr}</p>
            {#if entry}
              <p class="mt-1 text-xs text-gray-500">
                {formatDate(entry.created_at)} · {entry.changed_by ?? 'sistem'}
              </p>
              {#if entry.note}
                <p class="mt-1 text-sm text-gray-700">{entry.note}</p>
              {/if}
            {:else}
              <p class="mt-1 text-xs text-gray-400">Bu adım için güncelleme bekleniyor.</p>
            {/if}
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
