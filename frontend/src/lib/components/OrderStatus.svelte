<script lang="ts">
  export let order: any;

  const steps = [
    { status: 'created', label: 'Sipariş Alındı' },
    { status: 'payment_received', label: 'Domain Alınıyor' },
    { status: 'domain_purchased', label: 'Hosting Hazırlanıyor' },
    { status: 'hosting_setup', label: 'Tema Yükleniyor' },
    { status: 'completed', label: 'Tamamlandı' }
  ];

  function getStepIndex(status: string) {
    return steps.findIndex(s => s.status === status);
  }

  $: currentStepIndex = getStepIndex(order.status);
</script>

<div class="bg-white shadow rounded-lg p-6">
  <h2 class="text-xl font-bold mb-6 text-center">Sipariş Durumu</h2>

  <div class="relative">
    <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
      <div 
        style="width: {(currentStepIndex + 1) / steps.length * 100}%" 
        class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500 transition-all duration-500"
      ></div>
    </div>
    <div class="flex justify-between text-xs sm:text-sm text-gray-600">
      {#each steps as step, i}
        <div class="text-center {i <= currentStepIndex ? 'text-blue-600 font-bold' : ''}">
          {step.label}
        </div>
      {/each}
    </div>
  </div>

  <div class="mt-8 text-center">
    {#if order.status === 'completed'}
      <div class="text-green-600 text-xl font-bold mb-2">🎉 Siteniz Hazır!</div>
      <p class="text-gray-600 mb-4">Web siteniz yayında.</p>
      <a href={`https://${order.domain_name}`} target="_blank" class="inline-block bg-green-600 text-white px-6 py-2 rounded font-bold hover:bg-green-700">
        Siteyi Ziyaret Et
      </a>
    {:else}
      <div class="text-blue-600 text-xl font-bold mb-2">🔄 İşlem Devam Ediyor</div>
      <p class="text-gray-600">
        {#if order.status === 'created'}
          Ödeme onayı bekleniyor.
        {:else if order.status === 'payment_received'}
          Ödemeniz alındı. Domain tescil işlemleri başlatılıyor.
        {:else if order.status === 'domain_purchased'}
          Domain tescil edildi. Hosting hesabınız hazırlanıyor.
        {:else if order.status === 'hosting_setup'}
          Hosting hazır. Seçtiğiniz tema ve eklentiler yükleniyor.
        {/if}
      </p>
    {/if}
  </div>
</div>
