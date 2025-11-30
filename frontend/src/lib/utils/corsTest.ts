/**
 * Frontend CORS Testing Utilities
 * Comprehensive client-side CORS testing tools
 */

import { API_URL } from '$lib/api';

export interface CorsTestResult {
  test: string;
  success: boolean;
  duration: number;
  error?: string;
  details?: any;
  headers?: Record<string, string>;
}

export interface CorsTestSuite {
  timestamp: string;
  apiUrl: string;
  origin: string;
  results: CorsTestResult[];
  summary: {
    total: number;
    passed: number;
    failed: number;
    successRate: number;
  };
}

/**
 * Run a single CORS test
 */
async function runTest(
  name: string,
  testFn: () => Promise<any>
): Promise<CorsTestResult> {
  const startTime = performance.now();

  try {
    const result = await testFn();
    const duration = performance.now() - startTime;

    return {
      test: name,
      success: true,
      duration,
      details: result
    };
  } catch (error: any) {
    const duration = performance.now() - startTime;

    return {
      test: name,
      success: false,
      duration,
      error: error.message || 'Unknown error',
      details: error
    };
  }
}

/**
 * Test 1: Simple GET request
 */
async function testSimpleGet(): Promise<any> {
  const response = await fetch(`${API_URL}/test/cors-diagnostics.php?test=basic`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json'
    }
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
  }

  return await response.json();
}

/**
 * Test 2: Preflight OPTIONS request
 */
async function testPreflight(): Promise<any> {
  const response = await fetch(`${API_URL}/test/cors-diagnostics.php?test=preflight`, {
    method: 'OPTIONS',
    headers: {
      'Content-Type': 'application/json',
      'Access-Control-Request-Method': 'POST',
      'Access-Control-Request-Headers': 'Content-Type, Authorization'
    }
  });

  return {
    status: response.status,
    statusText: response.statusText,
    headers: Object.fromEntries(response.headers.entries())
  };
}

/**
 * Test 3: POST with JSON data
 */
async function testPostJson(): Promise<any> {
  const testData = {
    test: 'CORS POST test',
    timestamp: new Date().toISOString(),
    data: { foo: 'bar', number: 123 }
  };

  const response = await fetch(`${API_URL}/test/cors-endpoints.php?endpoint=post-json`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(testData)
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
  }

  return await response.json();
}

/**
 * Test 4: Request with credentials (cookies)
 */
async function testWithCredentials(): Promise<any> {
  const response = await fetch(`${API_URL}/test/cors-endpoints.php?endpoint=with-cookies`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json'
    },
    credentials: 'include'
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
  }

  return await response.json();
}

/**
 * Test 5: Request with Authorization header
 */
async function testWithAuth(token?: string): Promise<any> {
  const testToken = token || 'test-token-12345';

  const response = await fetch(`${API_URL}/test/cors-endpoints.php?endpoint=with-auth`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${testToken}`
    }
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
  }

  return await response.json();
}

/**
 * Test 6: Custom headers
 */
async function testCustomHeaders(): Promise<any> {
  const response = await fetch(`${API_URL}/test/cors-diagnostics.php?test=custom-headers`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-Test-Header': 'CustomValue',
      'X-Custom-Header': 'TestValue'
    }
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
  }

  return await response.json();
}

/**
 * Test 7: Different HTTP methods
 */
async function testMethods(): Promise<any> {
  const methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
  const results: any = {};

  for (const method of methods) {
    try {
      const response = await fetch(`${API_URL}/test/cors-diagnostics.php?test=methods`, {
        method,
        headers: {
          'Content-Type': 'application/json'
        },
        body: method !== 'GET' ? JSON.stringify({ test: true }) : undefined
      });

      results[method] = {
        success: response.ok,
        status: response.status,
        statusText: response.statusText
      };
    } catch (error: any) {
      results[method] = {
        success: false,
        error: error.message
      };
    }
  }

  return results;
}

/**
 * Test 8: Origin validation
 */
async function testOriginValidation(): Promise<any> {
  const response = await fetch(`${API_URL}/test/cors-diagnostics.php?test=origin-validation`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json'
    }
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
  }

  return await response.json();
}

/**
 * Test 9: Error handling
 */
async function testErrorHandling(): Promise<any> {
  const response = await fetch(`${API_URL}/test/cors-endpoints.php?endpoint=error`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json'
    }
  });

  const data = await response.json();

  return {
    status: response.status,
    corsHeadersPresent: response.headers.has('access-control-allow-origin'),
    data
  };
}

/**
 * Test 10: Large payload
 */
async function testLargePayload(): Promise<any> {
  const response = await fetch(`${API_URL}/test/cors-endpoints.php?endpoint=large-payload`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json'
    }
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
  }

  const data = await response.json();

  return {
    success: data.success,
    dataSize: JSON.stringify(data).length,
    itemCount: data.large_data?.length || 0
  };
}

/**
 * Test all existing API endpoints for CORS
 */
async function testExistingEndpoints(): Promise<any> {
  const endpoints = [
    { path: '/packages', method: 'GET' },
    { path: '/auth/login', method: 'POST', body: { email: 'test@test.com', password: 'test' } },
    { path: '/check-domain', method: 'POST', body: { domain: 'test', tld: 'com' } },
    { path: '/contact', method: 'POST', body: { name: 'Test', email: 'test@test.com', message: 'Test' } }
  ];

  const results: any = {};

  for (const endpoint of endpoints) {
    try {
      const response = await fetch(`${API_URL}${endpoint.path}`, {
        method: endpoint.method,
        headers: {
          'Content-Type': 'application/json'
        },
        body: endpoint.body ? JSON.stringify(endpoint.body) : undefined
      });

      results[endpoint.path] = {
        success: true,
        status: response.status,
        hasCorsHeaders: response.headers.has('access-control-allow-origin'),
        corsOrigin: response.headers.get('access-control-allow-origin')
      };
    } catch (error: any) {
      results[endpoint.path] = {
        success: false,
        error: error.message
      };
    }
  }

  return results;
}

/**
 * Run complete CORS test suite
 */
export async function runCorsTestSuite(authToken?: string): Promise<CorsTestSuite> {
  const results: CorsTestResult[] = [];

  console.log('🔍 Starting CORS Test Suite...');

  // Run all tests
  results.push(await runTest('Simple GET Request', testSimpleGet));
  results.push(await runTest('Preflight OPTIONS Request', testPreflight));
  results.push(await runTest('POST with JSON Data', testPostJson));
  results.push(await runTest('Request with Credentials', testWithCredentials));
  results.push(await runTest('Request with Authorization', () => testWithAuth(authToken)));
  results.push(await runTest('Custom Headers', testCustomHeaders));
  results.push(await runTest('Different HTTP Methods', testMethods));
  results.push(await runTest('Origin Validation', testOriginValidation));
  results.push(await runTest('Error Handling with CORS', testErrorHandling));
  results.push(await runTest('Large Payload', testLargePayload));
  results.push(await runTest('Existing API Endpoints', testExistingEndpoints));

  // Calculate summary
  const passed = results.filter(r => r.success).length;
  const failed = results.filter(r => !r.success).length;

  const suite: CorsTestSuite = {
    timestamp: new Date().toISOString(),
    apiUrl: API_URL,
    origin: typeof window !== 'undefined' ? window.location.origin : 'unknown',
    results,
    summary: {
      total: results.length,
      passed,
      failed,
      successRate: (passed / results.length) * 100
    }
  };

  console.log('✅ CORS Test Suite Complete', suite.summary);

  return suite;
}

/**
 * Quick CORS check for debugging
 */
export async function quickCorsCheck(): Promise<boolean> {
  try {
    const response = await fetch(`${API_URL}/test/cors-diagnostics.php?test=basic`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json'
      }
    });

    return response.ok && response.headers.has('access-control-allow-origin');
  } catch (error) {
    console.error('Quick CORS check failed:', error);
    return false;
  }
}

/**
 * Get detailed CORS diagnostics
 */
export async function getCorsdiagnostics(): Promise<any> {
  const response = await fetch(`${API_URL}/test/cors-diagnostics.php`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json'
    }
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
  }

  return await response.json();
}
