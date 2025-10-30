<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Country API - HNG</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        
        h1 {
            color: #667eea;
            text-align: center;
            margin-bottom: 10px;
            font-size: 2.5em;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        
        .endpoints {
            margin: 30px 0;
        }
        
        .endpoint {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .endpoint-method {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.85em;
            margin-right: 10px;
        }
        
        .endpoint-url {
            font-family: 'Courier New', monospace;
            color: #333;
            font-size: 0.95em;
        }
        
        .btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1em;
            cursor: pointer;
            margin: 10px;
            transition: all 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.2);
        }
        
        .btn-success {
            background: #28a745;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-info {
            background: #17a2b8;
        }
        
        .btn-info:hover {
            background: #138496;
        }
        
        .btn-danger {
            background: #dc3545;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-container {
            text-align: center;
            margin-top: 30px;
        }
        
        #response {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            display: none;
        }
        
        .loading {
            text-align: center;
            color: #667eea;
            padding: 20px;
        }
        
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        
        .status {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
            </style>
    </head>
<body>
    <div class="container">
        <h1>🌍 Country API</h1>
        <p class="subtitle">HNG Task - RESTful Country Management</p>
        
        <div class="status" id="status">
            <strong>✓ Status:</strong> API Running on <code>http://localhost:8000</code>
        </div>
        
        <div class="endpoints">
            <h3 style="margin-bottom: 20px; color: #667eea;">Available Endpoints:</h3>
            
            <div class="endpoint">
                <span class="endpoint-method">POST</span>
                <span class="endpoint-url">/api/countries/refresh</span>
            </div>
            
            <div class="endpoint">
                <span class="endpoint-method">GET</span>
                <span class="endpoint-url">/api/countries</span>
            </div>
            
            <div class="endpoint">
                <span class="endpoint-method">GET</span>
                <span class="endpoint-url">/api/countries/image</span>
            </div>
            
            <div class="endpoint">
                <span class="endpoint-method">GET</span>
                <span class="endpoint-url">/api/countries/{name}</span>
            </div>
            
            <div class="endpoint">
                <span class="endpoint-method">DELETE</span>
                <span class="endpoint-url">/api/countries/{name}</span>
            </div>
            
            <div class="endpoint">
                <span class="endpoint-method">GET</span>
                <span class="endpoint-url">/api/status</span>
            </div>
                </div>
        
        <div class="btn-container">
            <button class="btn btn-success" onclick="testEndpoint('POST', '/api/countries/refresh')">
                Refresh Countries
            </button>
            <button class="btn btn-success" onclick="testEndpoint('GET', '/api/countries')">
                Get All Countries
            </button>
            <button class="btn btn-info" onclick="testEndpoint('GET', '/api/countries/image')">
                Get Country Image
            </button>
            <button class="btn btn-info" onclick="testEndpoint('GET', '/api/countries/Nigeria')">
                Get Nigeria
            </button>
            <button class="btn btn-danger" onclick="testEndpoint('DELETE', '/api/countries/Nigeria')">
                Delete Nigeria
            </button>
            <button class="btn btn-info" onclick="testEndpoint('GET', '/api/status')">
                API Status
            </button>
        </div>

        <div id="response"></div>
    </div>
    
    <script>
        async function testEndpoint(method, endpoint) {
            const responseDiv = document.getElementById('response');
            responseDiv.style.display = 'block';
            responseDiv.innerHTML = '<div class="loading">⏳ Loading...</div>';
            
            try {
                // If requesting the image endpoint, open it directly in a new tab
                if (endpoint.includes('/countries/image')) {
                    window.open(endpoint, '_blank');
                    responseDiv.innerHTML = `<strong>Opened image in a new tab:</strong> <a href="${endpoint}" target="_blank">${endpoint}</a>`;
                    return;
                }

                const options = {
                    method: method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                };
                
                const response = await fetch(endpoint, options);

                // Handle non-JSON responses gracefully
                const contentType = response.headers.get('content-type') || '';
                if (!contentType.includes('application/json')) {
                    const text = await response.text();
                    responseDiv.innerHTML = `
                        <strong>Status: ${response.status}</strong>
                        <pre>${text}</pre>
                    `;
                    return;
                }

                const data = await response.json();
                
                responseDiv.innerHTML = `
                    <strong style="color: #28a745;">Status: ${response.status}</strong>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
            } catch (error) {
                responseDiv.innerHTML = `
                    <div class="error">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            }
        }
    </script>
    </body>
</html>