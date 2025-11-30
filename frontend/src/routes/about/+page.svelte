<script>
  import { t } from 'svelte-i18n';
</script>

<svelte:head>
  <title>{$t('common.about')} - Bezmidar</title>
</svelte:head>

<div class="bg-white py-16 px-4 overflow-hidden sm:px-6 lg:px-8 lg:py-24">
  <div class="relative max-w-xl mx-auto">
    <div class="text-center">
      <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 sm:text-4xl">
        {$t('common.about')}
      </h2>
      <p class="mt-4 text-lg leading-6 text-gray-500">
        Bezmidar Sitebuilder, işletmelerin dijital dünyada var olmalarını kolaylaştıran modern bir platformdur.
      </p>
    </div>
    
    <div class="mt-12">
      <p class="text-gray-600">
        Amacımız, teknik bilgi gerektirmeden herkesin profesyonel bir web sitesine sahip olmasını sağlamaktır.
        Güçlü altyapımız, modern tasarımlarımız ve kullanıcı dostu arayüzümüz ile işinizi büyütmenize yardımcı oluyoruz.
      </p>
    </div>
  </div>
</div>
