<script lang="ts">
  import { onMount } from 'svelte';
  import { runCorsTestSuite, quickCorsCheck, getCorsdiagnostics, type CorsTestSuite } from '$lib/utils/corsTest';
  import { authStore } from '$lib/stores/auth';
  import { goto } from '$app/navigation';

  let testSuite: CorsTestSuite | null = null;
  let diagnostics: any = null;
  let isRunning = false;
  let quickCheckResult: boolean | null = null;
  let selectedTest: string | null = null;

  onMount(() => {
    // Check if user is authenticated
    const unsubscribe = authStore.subscribe(value => {
      if (!value.isAuthenticated || value.user?.role !== 'admin') {
        goto('/admin/login');
      }
    });

    return unsubscribe;
  });

  async function runQuickCheck() {
    quickCheckResult = await quickCorsCheck();
  }

  async function runFullTest() {
    isRunning = true;
    testSuite = null;
    selectedTest = null;

    try {
      const token = $authStore.token || undefined;
      testSuite = await runCorsTestSuite(token);
    } catch (error) {
      console.error('Test suite failed:', error);
    } finally {
      isRunning = false;
    }
  }

  async function loadDiagnostics() {
    try {
      diagnostics = await getCorsdiagnostics();
    } catch (error) {
      console.error('Failed to load diagnostics:', error);
    }
  }

  function selectTest(testName: string) {
    selectedTest = selectedTest === testName ? null : testName;
  }

  function getStatusColor(success: boolean): string {
    return success ? 'text-green-600' : 'text-red-600';
  }

  function getStatusIcon(success: boolean): string {
    return success ? '✓' : '✗';
  }

  function downloadResults() {
    if (!testSuite) return;

    const dataStr = JSON.stringify(testSuite, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,' + encodeURIComponent(dataStr);
    const exportFileDefaultName = `cors-test-results-${new Date().toISOString()}.json`;

    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
  }
</script>

<div class="container mx-auto px-4 py-8 max-w-6xl">
  <div class="mb-8">
    <h1 class="text-3xl font-bold mb-2">CORS Test Dashboard</h1>
    <p class="text-gray-600">Comprehensive CORS testing and diagnostics</p>
  </div>

  <!-- Quick Actions -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <button
      on:click={runQuickCheck}
      class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors"
    >
      Quick CORS Check
    </button>

    <button
      on:click={runFullTest}
      disabled={isRunning}
      class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
    >
      {isRunning ? 'Running Tests...' : 'Run Full Test Suite'}
    </button>

    <button
      on:click={loadDiagnostics}
      class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-lg font-medium transition-colors"
    >
      Load Diagnostics
    </button>
  </div>

  <!-- Quick Check Result -->
  {#if quickCheckResult !== null}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <h2 class="text-xl font-semibold mb-4">Quick Check Result</h2>
      <div class="flex items-center gap-3">
        <span class="text-4xl">{quickCheckResult ? '✅' : '❌'}</span>
        <div>
          <p class="font-medium {getStatusColor(quickCheckResult)}">
            {quickCheckResult ? 'CORS is working!' : 'CORS has issues'}
          </p>
          <p class="text-sm text-gray-600">
            {quickCheckResult
              ? 'Basic CORS connectivity is functioning properly'
              : 'Unable to establish CORS connection'}
          </p>
        </div>
      </div>
    </div>
  {/if}

  <!-- Test Suite Results -->
  {#if testSuite}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold">Test Results</h2>
        <button
          on:click={downloadResults}
          class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
        >
          📥 Download Results
        </button>
      </div>

      <!-- Summary -->
      <div class="grid grid-cols-4 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
        <div class="text-center">
          <p class="text-2xl font-bold">{testSuite.summary.total}</p>
          <p class="text-sm text-gray-600">Total Tests</p>
        </div>
        <div class="text-center">
          <p class="text-2xl font-bold text-green-600">{testSuite.summary.passed}</p>
          <p class="text-sm text-gray-600">Passed</p>
        </div>
        <div class="text-center">
          <p class="text-2xl font-bold text-red-600">{testSuite.summary.failed}</p>
          <p class="text-sm text-gray-600">Failed</p>
        </div>
        <div class="text-center">
          <p class="text-2xl font-bold">{testSuite.summary.successRate.toFixed(1)}%</p>
          <p class="text-sm text-gray-600">Success Rate</p>
        </div>
      </div>

      <!-- Test Details -->
      <div class="space-y-2">
        {#each testSuite.results as result, i}
          <div class="border rounded-lg overflow-hidden">
            <button
              on:click={() => selectTest(result.test)}
              class="w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center gap-3">
                <span class="text-xl {getStatusColor(result.success)}">
                  {getStatusIcon(result.success)}
                </span>
                <span class="font-medium">{result.test}</span>
                <span class="text-sm text-gray-500">{result.duration.toFixed(0)}ms</span>
              </div>
              <span class="text-gray-400">{selectedTest === result.test ? '▼' : '▶'}</span>
            </button>

            {#if selectedTest === result.test}
              <div class="px-4 py-3 bg-gray-50 border-t">
                <div class="space-y-2">
                  <div>
                    <p class="text-sm font-medium text-gray-700">Status:</p>
                    <p class="{getStatusColor(result.success)} font-medium">
                      {result.success ? 'Passed' : 'Failed'}
                    </p>
                  </div>

                  {#if result.error}
                    <div>
                      <p class="text-sm font-medium text-gray-700">Error:</p>
                      <p class="text-red-600 text-sm font-mono">{result.error}</p>
                    </div>
                  {/if}

                  {#if result.details}
                    <div>
                      <p class="text-sm font-medium text-gray-700 mb-2">Details:</p>
                      <pre class="bg-gray-900 text-green-400 p-3 rounded text-xs overflow-x-auto">{JSON.stringify(result.details, null, 2)}</pre>
                    </div>
                  {/if}
                </div>
              </div>
            {/if}
          </div>
        {/each}
      </div>
    </div>
  {/if}

  <!-- Diagnostics -->
  {#if diagnostics}
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-2xl font-semibold mb-6">CORS Diagnostics</h2>

      <!-- Request Info -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-3">Request Information</h3>
        <div class="bg-gray-50 p-4 rounded-lg">
          <dl class="grid grid-cols-2 gap-3 text-sm">
            {#each Object.entries(diagnostics.request_info || {}) as [key, value]}
              <div>
                <dt class="font-medium text-gray-700">{key}:</dt>
                <dd class="text-gray-900 font-mono">{value}</dd>
              </div>
            {/each}
          </dl>
        </div>
      </div>

      <!-- CORS Headers -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-3">CORS Headers Sent</h3>
        <div class="bg-gray-50 p-4 rounded-lg">
          <dl class="space-y-2 text-sm">
            {#each Object.entries(diagnostics.cors_headers_sent || {}) as [key, value]}
              <div class="flex">
                <dt class="font-medium text-gray-700 w-64">{key}:</dt>
                <dd class="text-gray-900 font-mono flex-1">{value}</dd>
              </div>
            {/each}
          </dl>
        </div>
      </div>

      <!-- Endpoint Scan -->
      {#if diagnostics.endpoint_scan && diagnostics.endpoint_scan.length > 0}
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-3">Endpoint Scan Results</h3>
          <div class="space-y-2">
            {#each diagnostics.endpoint_scan as endpoint}
              <div class="bg-gray-50 p-3 rounded-lg">
                <p class="font-mono text-sm mb-2">{endpoint.file}</p>
                <div class="flex flex-wrap gap-2 text-xs">
                  <span class="px-2 py-1 rounded {endpoint.has_cors_include ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'}">
                    {endpoint.has_cors_include ? '✓' : '✗'} CORS Include
                  </span>
                  <span class="px-2 py-1 rounded {endpoint.has_manual_cors ? 'bg-blue-100 text-blue-700' : 'bg-gray-200 text-gray-600'}">
                    {endpoint.has_manual_cors ? '✓' : '✗'} Manual CORS
                  </span>
                  <span class="px-2 py-1 rounded {endpoint.has_options_handler ? 'bg-purple-100 text-purple-700' : 'bg-gray-200 text-gray-600'}">
                    {endpoint.has_options_handler ? '✓' : '✗'} OPTIONS Handler
                  </span>
                  {#if endpoint.issue}
                    <span class="px-2 py-1 rounded bg-red-100 text-red-700">
                      ⚠️ {endpoint.issue}
                    </span>
                  {/if}
                </div>
              </div>
            {/each}
          </div>
        </div>
      {/if}

      <!-- Full Diagnostics -->
      <div>
        <h3 class="text-lg font-semibold mb-3">Full Diagnostics Data</h3>
        <pre class="bg-gray-900 text-green-400 p-4 rounded text-xs overflow-x-auto">{JSON.stringify(diagnostics, null, 2)}</pre>
      </div>
    </div>
  {/if}

  <!-- Loading State -->
  {#if isRunning}
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-8 max-w-md">
        <div class="flex items-center gap-4">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
          <div>
            <p class="font-semibold text-lg">Running CORS Tests</p>
            <p class="text-sm text-gray-600">Please wait...</p>
          </div>
        </div>
      </div>
    </div>
  {/if}
</div>

<style>
  /* Custom scrollbar for code blocks */
  pre::-webkit-scrollbar {
    height: 8px;
  }

  pre::-webkit-scrollbar-track {
    background: #1f2937;
  }

  pre::-webkit-scrollbar-thumb {
    background: #4b5563;
    border-radius: 4px;
  }

  pre::-webkit-scrollbar-thumb:hover {
    background: #6b7280;
  }
</style>
