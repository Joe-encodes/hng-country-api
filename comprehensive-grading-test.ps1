# ============================================================================
# COMPREHENSIVE API GRADING SCRIPT - Country Currency & Exchange API
# Total Points: 100
# ============================================================================

$baseUrl = "http://localhost:8000/api"
$global:totalPoints = 0
$global:maxPoints = 100
$global:testResults = @()
$global:categoryScores = @{}

Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host "COMPREHENSIVE API GRADING - Country Currency & Exchange API" -ForegroundColor Cyan
Write-Host "Total Possible Points: $maxPoints" -ForegroundColor Cyan
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

function Add-TestResult {
    param(
        [string]$Category,
        [string]$TestName,
        [int]$Points,
        [int]$MaxPoints,
        [string]$Status,
        [string]$Details = ""
    )
    
    $global:testResults += [PSCustomObject]@{
        Category = $Category
        TestName = $TestName
        Points = $Points
        MaxPoints = $MaxPoints
        Status = $Status
        Details = $Details
    }
    
    $global:totalPoints += $Points
    
    if (-not $global:categoryScores.ContainsKey($Category)) {
        $global:categoryScores[$Category] = @{ Earned = 0; Max = 0 }
    }
    $global:categoryScores[$Category].Earned += $Points
    $global:categoryScores[$Category].Max += $MaxPoints
}

function Test-Endpoint {
    param(
        [string]$Method,
        [string]$Url,
        [object]$Body = $null,
        [int]$ExpectedStatus = 200,
        [int]$TimeoutSeconds = 120
    )
    
    try {
        $params = @{
            Uri = $Url
            Method = $Method
            ContentType = "application/json"
            ErrorAction = "Stop"
            TimeoutSec = $TimeoutSeconds
        }
        
        if ($Body) {
            $params.Body = ($Body | ConvertTo-Json -Depth 10)
        }
        
        $response = Invoke-WebRequest @params
        $statusCode = $response.StatusCode
        
        $content = $null
        if ($response.Content) {
            try {
                $content = $response.Content | ConvertFrom-Json
            } catch {
                $content = $response.Content
            }
        }
        
        return @{
            Success = ($statusCode -eq $ExpectedStatus)
            StatusCode = $statusCode
            Content = $content
            RawContent = $response.Content
            Headers = $response.Headers
        }
        
    } catch {
        $statusCode = 0
        if ($_.Exception.Response) {
            $statusCode = [int]$_.Exception.Response.StatusCode
        }
        
        $errorContent = $null
        try {
            if ($_.ErrorDetails.Message) {
                $errorContent = $_.ErrorDetails.Message | ConvertFrom-Json
            }
        } catch {
            $errorContent = $_.ErrorDetails.Message
        }
        
        return @{
            Success = ($statusCode -eq $ExpectedStatus)
            StatusCode = $statusCode
            Content = $errorContent
            Error = $_.Exception.Message
        }
    }
}

# ============================================================================
# CATEGORY 1: BASIC ENDPOINT EXISTENCE (15 points)
# ============================================================================
Write-Host "`n[CATEGORY 1] BASIC ENDPOINT EXISTENCE (15 points)" -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Yellow

# Test 1.1: GET /status exists (3 points)
Write-Host "`nTest 1.1: GET /status endpoint exists" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/status" -ExpectedStatus 200
if ($result.Success) {
    Add-TestResult -Category "Basic Endpoints" -TestName "GET /status exists" -Points 3 -MaxPoints 3 -Status "PASS" -Details "Endpoint responds with 200"
    Write-Host "  [+] PASS (3/3 points)" -ForegroundColor Green
} else {
    Add-TestResult -Category "Basic Endpoints" -TestName "GET /status exists" -Points 0 -MaxPoints 3 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/3 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# Test 1.2: POST /countries/refresh exists (3 points)
Write-Host "`nTest 1.2: POST /countries/refresh endpoint exists" -ForegroundColor Cyan
$result = Test-Endpoint -Method "POST" -Url "$baseUrl/countries/refresh" -ExpectedStatus 200
if ($result.Success -or $result.StatusCode -eq 503) {
    Add-TestResult -Category "Basic Endpoints" -TestName "POST /countries/refresh exists" -Points 3 -MaxPoints 3 -Status "PASS" -Details "Endpoint exists"
    Write-Host "  [+] PASS (3/3 points)" -ForegroundColor Green
} else {
    Add-TestResult -Category "Basic Endpoints" -TestName "POST /countries/refresh exists" -Points 0 -MaxPoints 3 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/3 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# Test 1.3: GET /countries exists (3 points)
Write-Host "`nTest 1.3: GET /countries endpoint exists" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries" -ExpectedStatus 200
if ($result.Success) {
    Add-TestResult -Category "Basic Endpoints" -TestName "GET /countries exists" -Points 3 -MaxPoints 3 -Status "PASS" -Details "Endpoint responds with 200"
    Write-Host "  [+] PASS (3/3 points)" -ForegroundColor Green
} else {
    Add-TestResult -Category "Basic Endpoints" -TestName "GET /countries exists" -Points 0 -MaxPoints 3 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/3 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# Test 1.4: GET /countries/:name exists (3 points)
Write-Host "`nTest 1.4: GET /countries/:name endpoint exists" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries/TestCountry" -ExpectedStatus 404
if ($result.Success -or $result.StatusCode -eq 200) {
    Add-TestResult -Category "Basic Endpoints" -TestName "GET /countries/:name exists" -Points 3 -MaxPoints 3 -Status "PASS" -Details "Endpoint exists"
    Write-Host "  [+] PASS (3/3 points)" -ForegroundColor Green
} else {
    Add-TestResult -Category "Basic Endpoints" -TestName "GET /countries/:name exists" -Points 0 -MaxPoints 3 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/3 points) - Endpoint may not exist" -ForegroundColor Red
}

# Test 1.5: DELETE /countries/:name exists (3 points)
Write-Host "`nTest 1.5: DELETE /countries/:name endpoint exists" -ForegroundColor Cyan
$result = Test-Endpoint -Method "DELETE" -Url "$baseUrl/countries/TestCountry" -ExpectedStatus 404
if ($result.Success -or $result.StatusCode -eq 200) {
    Add-TestResult -Category "Basic Endpoints" -TestName "DELETE /countries/:name exists" -Points 3 -MaxPoints 3 -Status "PASS" -Details "Endpoint exists"
    Write-Host "  [+] PASS (3/3 points)" -ForegroundColor Green
} else {
    Add-TestResult -Category "Basic Endpoints" -TestName "DELETE /countries/:name exists" -Points 0 -MaxPoints 3 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/3 points) - Endpoint may not exist" -ForegroundColor Red
}

# ============================================================================
# CATEGORY 2: DATA REFRESH & EXTERNAL API INTEGRATION (20 points)
# ============================================================================
Write-Host "`n`n[CATEGORY 2] DATA REFRESH & EXTERNAL API INTEGRATION (20 points)" -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Yellow

# Test 2.1: POST /countries/refresh successfully fetches and stores data (10 points)
Write-Host "`nTest 2.1: POST /countries/refresh fetches and stores data" -ForegroundColor Cyan
Write-Host "  (This may take 30-60 seconds...)" -ForegroundColor Gray
$result = Test-Endpoint -Method "POST" -Url "$baseUrl/countries/refresh" -ExpectedStatus 200 -TimeoutSeconds 120
if ($result.Success) {
    Add-TestResult -Category "Data Refresh" -TestName "Refresh fetches and stores data" -Points 10 -MaxPoints 10 -Status "PASS" -Details "Data refreshed successfully"
    Write-Host "  [+] PASS (10/10 points)" -ForegroundColor Green
} elseif ($result.StatusCode -eq 503) {
    Add-TestResult -Category "Data Refresh" -TestName "Refresh fetches and stores data" -Points 5 -MaxPoints 10 -Status "PARTIAL" -Details "503 error (External API issue)"
    Write-Host "  [~] PARTIAL (5/10 points) - 503 Service Unavailable (External API may be down)" -ForegroundColor Yellow
} else {
    Add-TestResult -Category "Data Refresh" -TestName "Refresh fetches and stores data" -Points 0 -MaxPoints 10 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/10 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# Test 2.2: Verify data was stored in database (5 points)
Write-Host "`nTest 2.2: Verify data was stored in database" -ForegroundColor Cyan
Start-Sleep -Seconds 2
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries" -ExpectedStatus 200
if ($result.Success -and $result.Content -is [Array] -and $result.Content.Count -gt 0) {
    Add-TestResult -Category "Data Refresh" -TestName "Data stored in database" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Found $($result.Content.Count) countries"
    Write-Host "  [+] PASS (5/5 points) - Found $($result.Content.Count) countries" -ForegroundColor Green
    $global:sampleCountry = $result.Content[0]
} else {
    Add-TestResult -Category "Data Refresh" -TestName "Data stored in database" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "No data found"
    Write-Host "  [-] FAIL (0/5 points) - No data found in database" -ForegroundColor Red
}

# Test 2.3: Status endpoint shows updated timestamp (5 points)
Write-Host "`nTest 2.3: Status endpoint shows updated timestamp" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/status" -ExpectedStatus 200
if ($result.Success -and $result.Content.total_countries -gt 0 -and $result.Content.last_refreshed_at) {
    $timestamp = $result.Content.last_refreshed_at
    if ($timestamp -ne "0001-01-01T00:00:00" -and $timestamp -ne $null) {
        Add-TestResult -Category "Data Refresh" -TestName "Status shows updated timestamp" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Timestamp: $timestamp"
        Write-Host "  [+] PASS (5/5 points) - Timestamp: $timestamp" -ForegroundColor Green
    } else {
        Add-TestResult -Category "Data Refresh" -TestName "Status shows updated timestamp" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Invalid timestamp"
        Write-Host "  [-] FAIL (0/5 points) - Timestamp not updated" -ForegroundColor Red
    }
} else {
    Add-TestResult -Category "Data Refresh" -TestName "Status shows updated timestamp" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Status endpoint failed"
    Write-Host "  [-] FAIL (0/5 points) - Status endpoint failed" -ForegroundColor Red
}

# ============================================================================
# CATEGORY 3: REQUIRED FIELDS & DATA STRUCTURE (15 points)
# ============================================================================
Write-Host "`n`n[CATEGORY 3] REQUIRED FIELDS & DATA STRUCTURE (15 points)" -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Yellow

# Test 3.1: Country object has all required fields (10 points)
Write-Host "`nTest 3.1: Country object has all required fields" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries" -ExpectedStatus 200
if ($result.Success -and $result.Content.Count -gt 0) {
    $country = $result.Content[0]
    $requiredFields = @('id', 'name', 'population', 'currency_code', 'exchange_rate', 'estimated_gdp', 'last_refreshed_at')
    
    $missingFields = @()
    $points = 0
    
    foreach ($field in $requiredFields) {
        if ($null -eq $country.$field -or $country.$field -eq "") {
            $missingFields += $field
        }
    }
    
    if ($missingFields.Count -eq 0) {
        $points = 10
        Add-TestResult -Category "Data Structure" -TestName "All required fields present" -Points $points -MaxPoints 10 -Status "PASS" -Details "All fields present"
        Write-Host "  [+] PASS (10/10 points) - All required fields present" -ForegroundColor Green
    } else {
        $points = [Math]::Max(0, 10 - ($missingFields.Count * 2))
        Add-TestResult -Category "Data Structure" -TestName "All required fields present" -Points $points -MaxPoints 10 -Status "PARTIAL" -Details "Missing: $($missingFields -join ', ')"
        Write-Host "  [~] PARTIAL ($points/10 points) - Missing: $($missingFields -join ', ')" -ForegroundColor Yellow
    }
} else {
    Add-TestResult -Category "Data Structure" -TestName "All required fields present" -Points 0 -MaxPoints 10 -Status "FAIL" -Details "No data to check"
    Write-Host "  [-] FAIL (0/10 points) - No data to check" -ForegroundColor Red
}

# Test 3.2: Field types are correct (5 points)
Write-Host "`nTest 3.2: Field types are correct" -ForegroundColor Cyan
if ($result.Success -and $result.Content.Count -gt 0) {
    $country = $result.Content[0]
    $typeErrors = @()
    
    if ($country.id -isnot [int] -and $country.id -isnot [long]) { $typeErrors += "id (should be integer)" }
    if ($country.name -isnot [string]) { $typeErrors += "name (should be string)" }
    if ($country.population -isnot [int] -and $country.population -isnot [long]) { $typeErrors += "population (should be integer)" }
    if ($country.currency_code -and $country.currency_code -isnot [string]) { $typeErrors += "currency_code (should be string)" }
    if ($country.exchange_rate -and $country.exchange_rate -isnot [decimal] -and $country.exchange_rate -isnot [double]) { $typeErrors += "exchange_rate (should be number)" }
    if ($country.estimated_gdp -and $country.estimated_gdp -isnot [decimal] -and $country.estimated_gdp -isnot [double]) { $typeErrors += "estimated_gdp (should be number)" }
    
    if ($typeErrors.Count -eq 0) {
        Add-TestResult -Category "Data Structure" -TestName "Field types correct" -Points 5 -MaxPoints 5 -Status "PASS" -Details "All types correct"
        Write-Host "  [+] PASS (5/5 points) - All field types correct" -ForegroundColor Green
    } else {
        $points = [Math]::Max(0, 5 - $typeErrors.Count)
        Add-TestResult -Category "Data Structure" -TestName "Field types correct" -Points $points -MaxPoints 5 -Status "PARTIAL" -Details "Issues: $($typeErrors -join ', ')"
        Write-Host "  [~] PARTIAL ($points/5 points) - Type issues: $($typeErrors -join ', ')" -ForegroundColor Yellow
    }
} else {
    Add-TestResult -Category "Data Structure" -TestName "Field types correct" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "No data to check"
    Write-Host "  [-] FAIL (0/5 points)" -ForegroundColor Red
}

# ============================================================================
# CATEGORY 4: FILTERING & SORTING (15 points)
# ============================================================================
Write-Host "`n`n[CATEGORY 4] FILTERING & SORTING (15 points)" -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Yellow

# Test 4.1: Filter by region (5 points)
Write-Host "`nTest 4.1: Filter by region (?region=Africa)" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries?region=Africa" -ExpectedStatus 200
if ($result.Success) {
    if ($result.Content.Count -gt 0) {
        $allAfrican = $true
        foreach ($country in $result.Content) {
            if ($country.region -ne "Africa") {
                $allAfrican = $false
                break
            }
        }
        if ($allAfrican) {
            Add-TestResult -Category "Filtering & Sorting" -TestName "Filter by region" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Found $($result.Content.Count) African countries"
            Write-Host "  [+] PASS (5/5 points) - Found $($result.Content.Count) African countries" -ForegroundColor Green
        } else {
            Add-TestResult -Category "Filtering & Sorting" -TestName "Filter by region" -Points 2 -MaxPoints 5 -Status "PARTIAL" -Details "Filter not working correctly"
            Write-Host "  [~] PARTIAL (2/5 points) - Filter returned non-African countries" -ForegroundColor Yellow
        }
    } else {
        Add-TestResult -Category "Filtering & Sorting" -TestName "Filter by region" -Points 3 -MaxPoints 5 -Status "PARTIAL" -Details "No results (may be correct)"
        Write-Host "  [~] PARTIAL (3/5 points) - No results" -ForegroundColor Yellow
    }
} else {
    Add-TestResult -Category "Filtering & Sorting" -TestName "Filter by region" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/5 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# Test 4.2: Filter by currency (5 points)
Write-Host "`nTest 4.2: Filter by currency (?currency=NGN)" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries?currency=NGN" -ExpectedStatus 200
if ($result.Success) {
    if ($result.Content.Count -gt 0) {
        $allNGN = $true
        foreach ($country in $result.Content) {
            if ($country.currency_code -ne "NGN") {
                $allNGN = $false
                break
            }
        }
        if ($allNGN) {
            Add-TestResult -Category "Filtering & Sorting" -TestName "Filter by currency" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Found $($result.Content.Count) countries with NGN"
            Write-Host "  [+] PASS (5/5 points) - Found $($result.Content.Count) countries with NGN" -ForegroundColor Green
        } else {
            Add-TestResult -Category "Filtering & Sorting" -TestName "Filter by currency" -Points 2 -MaxPoints 5 -Status "PARTIAL" -Details "Filter not working correctly"
            Write-Host "  [~] PARTIAL (2/5 points) - Filter returned wrong currencies" -ForegroundColor Yellow
        }
    } else {
        Add-TestResult -Category "Filtering & Sorting" -TestName "Filter by currency" -Points 3 -MaxPoints 5 -Status "PARTIAL" -Details "No results"
        Write-Host "  [~] PARTIAL (3/5 points) - No results" -ForegroundColor Yellow
    }
} else {
    Add-TestResult -Category "Filtering & Sorting" -TestName "Filter by currency" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/5 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# Test 4.3: Sort by GDP descending (5 points)
Write-Host "`nTest 4.3: Sort by GDP descending (?sort=gdp_desc)" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries?sort=gdp_desc" -ExpectedStatus 200
if ($result.Success -and $result.Content.Count -gt 1) {
    $sorted = $true
    for ($i = 0; $i -lt ($result.Content.Count - 1); $i++) {
        if ($result.Content[$i].estimated_gdp -lt $result.Content[$i + 1].estimated_gdp) {
            $sorted = $false
            break
        }
    }
    if ($sorted) {
        Add-TestResult -Category "Filtering & Sorting" -TestName "Sort by GDP descending" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Correctly sorted"
        Write-Host "  [+] PASS (5/5 points) - Correctly sorted by GDP descending" -ForegroundColor Green
    } else {
        Add-TestResult -Category "Filtering & Sorting" -TestName "Sort by GDP descending" -Points 2 -MaxPoints 5 -Status "PARTIAL" -Details "Not correctly sorted"
        Write-Host "  [~] PARTIAL (2/5 points) - Not correctly sorted" -ForegroundColor Yellow
    }
} else {
    Add-TestResult -Category "Filtering & Sorting" -TestName "Sort by GDP descending" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/5 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# ============================================================================
# CATEGORY 5: CRUD OPERATIONS (15 points)
# ============================================================================
Write-Host "`n`n[CATEGORY 5] CRUD OPERATIONS (15 points)" -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Yellow

# Test 5.1: GET country by name (case-insensitive) (5 points)
Write-Host "`nTest 5.1: GET country by name (case-insensitive)" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries/nigeria" -ExpectedStatus 200
if ($result.Success -and $result.Content.name) {
    if ($result.Content.name -eq "Nigeria") {
        Add-TestResult -Category "CRUD Operations" -TestName "GET by name (case-insensitive)" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Found Nigeria"
        Write-Host "  [+] PASS (5/5 points) - Found Nigeria (case-insensitive)" -ForegroundColor Green
    } else {
        Add-TestResult -Category "CRUD Operations" -TestName "GET by name (case-insensitive)" -Points 3 -MaxPoints 5 -Status "PARTIAL" -Details "Wrong country returned"
        Write-Host "  [~] PARTIAL (3/5 points) - Returned: $($result.Content.name)" -ForegroundColor Yellow
    }
} else {
    Add-TestResult -Category "CRUD Operations" -TestName "GET by name (case-insensitive)" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/5 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# Test 5.2: GET non-existent country returns 404 (5 points)
Write-Host "`nTest 5.2: GET non-existent country returns 404" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries/NonExistentCountryXYZ123" -ExpectedStatus 404
if ($result.Success -and $result.Content.error) {
    Add-TestResult -Category "CRUD Operations" -TestName "404 for non-existent country" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Correct 404 response"
    Write-Host "  [+] PASS (5/5 points) - Returns 404 with error message" -ForegroundColor Green
} elseif ($result.StatusCode -eq 404) {
    Add-TestResult -Category "CRUD Operations" -TestName "404 for non-existent country" -Points 4 -MaxPoints 5 -Status "PARTIAL" -Details "404 but no error message"
    Write-Host "  [~] PARTIAL (4/5 points) - Returns 404 but no error message" -ForegroundColor Yellow
} else {
    Add-TestResult -Category "CRUD Operations" -TestName "404 for non-existent country" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Wrong status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/5 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# Test 5.3: DELETE country and verify (5 points)
Write-Host "`nTest 5.3: DELETE country and verify deletion" -ForegroundColor Cyan
$getResult = Test-Endpoint -Method "GET" -Url "$baseUrl/countries/Ghana" -ExpectedStatus 200
if ($getResult.Success) {
    $deleteResult = Test-Endpoint -Method "DELETE" -Url "$baseUrl/countries/Ghana" -ExpectedStatus 200
    if ($deleteResult.Success) {
        Start-Sleep -Seconds 1
        $verifyResult = Test-Endpoint -Method "GET" -Url "$baseUrl/countries/Ghana" -ExpectedStatus 404
        if ($verifyResult.Success) {
            Add-TestResult -Category "CRUD Operations" -TestName "DELETE country" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Successfully deleted Ghana"
            Write-Host "  [+] PASS (5/5 points) - Successfully deleted Ghana" -ForegroundColor Green
        } else {
            Add-TestResult -Category "CRUD Operations" -TestName "DELETE country" -Points 2 -MaxPoints 5 -Status "PARTIAL" -Details "Deleted but still exists"
            Write-Host "  [~] PARTIAL (2/5 points) - Deleted but country still exists" -ForegroundColor Yellow
        }
    } else {
        Add-TestResult -Category "CRUD Operations" -TestName "DELETE country" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "DELETE failed"
        Write-Host "  [-] FAIL (0/5 points) - DELETE failed" -ForegroundColor Red
    }
} else {
    Add-TestResult -Category "CRUD Operations" -TestName "DELETE country" -Points 0 -MaxPoints 5 -Status "SKIP" -Details "Ghana not found to delete"
    Write-Host "  [!] SKIP (0/5 points) - Ghana not found in database" -ForegroundColor Gray
}

# ============================================================================
# CATEGORY 6: ERROR HANDLING (10 points)
# ============================================================================
Write-Host "`n`n[CATEGORY 6] ERROR HANDLING (10 points)" -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Yellow

# Test 6.1: 404 error has correct format (5 points)
Write-Host "`nTest 6.1: 404 error has correct JSON format" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries/NonExistent" -ExpectedStatus 404
if ($result.Success -and $result.Content) {
    if ($result.Content.error -and $result.Content.error -match "not found") {
        Add-TestResult -Category "Error Handling" -TestName "404 error format" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Correct format"
        Write-Host "  [+] PASS (5/5 points) - Correct error format" -ForegroundColor Green
    } else {
        Add-TestResult -Category "Error Handling" -TestName "404 error format" -Points 2 -MaxPoints 5 -Status "PARTIAL" -Details "Has error field but wrong message"
        Write-Host "  [~] PARTIAL (2/5 points) - Has error but message doesn't match spec" -ForegroundColor Yellow
    }
} else {
    Add-TestResult -Category "Error Handling" -TestName "404 error format" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "No error object"
    Write-Host "  [-] FAIL (0/5 points) - No error object in response" -ForegroundColor Red
}

# Test 6.2: DELETE non-existent returns 404 (5 points)
Write-Host "`nTest 6.2: DELETE non-existent country returns 404" -ForegroundColor Cyan
$result = Test-Endpoint -Method "DELETE" -Url "$baseUrl/countries/NonExistentCountryXYZ" -ExpectedStatus 404
if ($result.Success) {
    Add-TestResult -Category "Error Handling" -TestName "DELETE non-existent 404" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Returns 404"
    Write-Host "  [+] PASS (5/5 points) - Correctly returns 404" -ForegroundColor Green
} else {
    Add-TestResult -Category "Error Handling" -TestName "DELETE non-existent 404" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/5 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# ============================================================================
# CATEGORY 7: IMAGE GENERATION (10 points)
# ============================================================================
Write-Host "`n`n[CATEGORY 7] IMAGE GENERATION (10 points)" -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Yellow

# Test 7.1: GET /countries/image endpoint exists (5 points)
Write-Host "`nTest 7.1: GET /countries/image endpoint exists" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries/image" -ExpectedStatus 200
if ($result.Success) {
    $contentType = $result.Headers.'Content-Type'
    if ($contentType -match "image/png" -or $contentType -match "image/jpeg") {
        Add-TestResult -Category "Image Generation" -TestName "Image endpoint exists" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Returns image"
        Write-Host "  [+] PASS (5/5 points) - Returns image ($contentType)" -ForegroundColor Green
    } else {
        Add-TestResult -Category "Image Generation" -TestName "Image endpoint exists" -Points 2 -MaxPoints 5 -Status "PARTIAL" -Details "Wrong content type"
        Write-Host "  [~] PARTIAL (2/5 points) - Wrong content type: $contentType" -ForegroundColor Yellow
    }
} else {
    Add-TestResult -Category "Image Generation" -TestName "Image endpoint exists" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Status: $($result.StatusCode)"
    Write-Host "  [-] FAIL (0/5 points) - Status: $($result.StatusCode)" -ForegroundColor Red
}

# Test 7.2: Image contains required information (5 points)
Write-Host "`nTest 7.2: Image generation after refresh" -ForegroundColor Cyan
Write-Host "  (Checking if cache/summary.png exists)" -ForegroundColor Gray
if (Test-Path "cache/summary.png") {
    Add-TestResult -Category "Image Generation" -TestName "Image file generated" -Points 5 -MaxPoints 5 -Status "PASS" -Details "Image file exists"
    Write-Host "  [+] PASS (5/5 points) - Image file exists at cache/summary.png" -ForegroundColor Green
} else {
    Add-TestResult -Category "Image Generation" -TestName "Image file generated" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Image file not found"
    Write-Host "  [-] FAIL (0/5 points) - Image file not found at cache/summary.png" -ForegroundColor Red
}

# ============================================================================
# CATEGORY 8: CURRENCY & EXCHANGE RATE HANDLING (10 points)
# ============================================================================
Write-Host "`n`n[CATEGORY 8] CURRENCY & EXCHANGE RATE HANDLING (10 points)" -ForegroundColor Yellow
Write-Host "================================================================" -ForegroundColor Yellow

# Test 8.1: Countries have valid exchange rates (5 points)
Write-Host "`nTest 8.1: Countries have valid exchange rates" -ForegroundColor Cyan
$result = Test-Endpoint -Method "GET" -Url "$baseUrl/countries" -ExpectedStatus 200
if ($result.Success -and $result.Content.Count -gt 0) {
    $withRates = ($result.Content | Where-Object { $_.exchange_rate -gt 0 }).Count
    $percentage = ($withRates / $result.Content.Count) * 100
    
    if ($percentage -gt 90) {
        Add-TestResult -Category "Currency Handling" -TestName "Valid exchange rates" -Points 5 -MaxPoints 5 -Status "PASS" -Details "$withRates/$($result.Content.Count) have rates"
        Write-Host "  [+] PASS (5/5 points) - $withRates/$($result.Content.Count) countries have exchange rates" -ForegroundColor Green
    } elseif ($percentage -gt 50) {
        $points = 3
        Add-TestResult -Category "Currency Handling" -TestName "Valid exchange rates" -Points $points -MaxPoints 5 -Status "PARTIAL" -Details "$withRates/$($result.Content.Count) have rates"
        Write-Host "  [~] PARTIAL ($points/5 points) - Only $withRates/$($result.Content.Count) have rates" -ForegroundColor Yellow
    } else {
        Add-TestResult -Category "Currency Handling" -TestName "Valid exchange rates" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "Too few valid rates"
        Write-Host "  [-] FAIL (0/5 points) - Too few countries have exchange rates" -ForegroundColor Red
    }
} else {
    Add-TestResult -Category "Currency Handling" -TestName "Valid exchange rates" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "No data"
    Write-Host "  [-] FAIL (0/5 points)" -ForegroundColor Red
}

# Test 8.2: Estimated GDP calculation is correct (5 points)
Write-Host "`nTest 8.2: Estimated GDP calculation is correct" -ForegroundColor Cyan
if ($result.Success -and $result.Content.Count -gt 0) {
    $validGDP = 0
    $checkedCountries = 0
    
    foreach ($country in ($result.Content | Select-Object -First 10)) {
        if ($country.exchange_rate -gt 0 -and $country.population -gt 0 -and $country.estimated_gdp -gt 0) {
            $checkedCountries++
            $minGDP = $country.population * 1000 / $country.exchange_rate
            $maxGDP = $country.population * 2000 / $country.exchange_rate
            
            if ($country.estimated_gdp -ge $minGDP * 0.99 -and $country.estimated_gdp -le $maxGDP * 1.01) {
                $validGDP++
            }
        }
    }
    
    if ($checkedCountries -gt 0) {
        $percentage = ($validGDP / $checkedCountries) * 100
        if ($percentage -gt 90) {
            Add-TestResult -Category "Currency Handling" -TestName "GDP calculation correct" -Points 5 -MaxPoints 5 -Status "PASS" -Details "$validGDP/$checkedCountries correct"
            Write-Host "  [+] PASS (5/5 points) - GDP calculations are correct" -ForegroundColor Green
        } elseif ($percentage -gt 50) {
            $points = 3
            Add-TestResult -Category "Currency Handling" -TestName "GDP calculation correct" -Points $points -MaxPoints 5 -Status "PARTIAL" -Details "$validGDP/$checkedCountries correct"
            Write-Host "  [~] PARTIAL ($points/5 points) - Some GDP calculations incorrect" -ForegroundColor Yellow
        } else {
            Add-TestResult -Category "Currency Handling" -TestName "GDP calculation correct" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "GDP formula wrong"
            Write-Host "  [-] FAIL (0/5 points) - GDP calculation formula appears incorrect" -ForegroundColor Red
        }
    } else {
        Add-TestResult -Category "Currency Handling" -TestName "GDP calculation correct" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "No valid data"
        Write-Host "  [-] FAIL (0/5 points) - No valid data to check" -ForegroundColor Red
    }
} else {
    Add-TestResult -Category "Currency Handling" -TestName "GDP calculation correct" -Points 0 -MaxPoints 5 -Status "FAIL" -Details "No data"
    Write-Host "  [-] FAIL (0/5 points)" -ForegroundColor Red
}

# ============================================================================
# FINAL SCORE CALCULATION
# ============================================================================
Write-Host "`n`n============================================================================" -ForegroundColor Cyan
Write-Host "FINAL GRADING REPORT" -ForegroundColor Cyan
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "CATEGORY BREAKDOWN:" -ForegroundColor Yellow
Write-Host "-------------------" -ForegroundColor Yellow
foreach ($category in $global:categoryScores.Keys | Sort-Object) {
    $earned = $global:categoryScores[$category].Earned
    $max = $global:categoryScores[$category].Max
    $percentage = [Math]::Round(($earned / $max) * 100, 1)
    $color = if ($percentage -ge 80) { "Green" } elseif ($percentage -ge 60) { "Yellow" } else { "Red" }
    Write-Host "$category : $earned / $max ($percentage%)" -ForegroundColor $color
}

Write-Host ""
Write-Host "============================================================================" -ForegroundColor Cyan
$finalPercentage = [Math]::Round(($global:totalPoints / $global:maxPoints) * 100, 1)
$grade = if ($finalPercentage -ge 90) { "A (Excellent)" } 
         elseif ($finalPercentage -ge 80) { "B (Good)" } 
         elseif ($finalPercentage -ge 70) { "C (Satisfactory)" } 
         elseif ($finalPercentage -ge 60) { "D (Pass)" } 
         else { "F (Fail)" }

Write-Host "TOTAL SCORE: $global:totalPoints / $global:maxPoints ($finalPercentage%)" -ForegroundColor Cyan
Write-Host "GRADE: $grade" -ForegroundColor Cyan
Write-Host "============================================================================" -ForegroundColor Cyan
Write-Host ""

$failedTests = $global:testResults | Where-Object { $_.Status -eq "FAIL" }
$partialTests = $global:testResults | Where-Object { $_.Status -eq "PARTIAL" }

if ($failedTests.Count -gt 0) {
    Write-Host "FAILED TESTS ($($failedTests.Count)):" -ForegroundColor Red
    Write-Host "-------------------" -ForegroundColor Red
    foreach ($test in $failedTests) {
        Write-Host "  [-] $($test.Category) - $($test.TestName)" -ForegroundColor Red
        Write-Host "      Details: $($test.Details)" -ForegroundColor Gray
    }
    Write-Host ""
}

if ($partialTests.Count -gt 0) {
    Write-Host "PARTIAL CREDIT TESTS ($($partialTests.Count)):" -ForegroundColor Yellow
    Write-Host "-------------------" -ForegroundColor Yellow
    foreach ($test in $partialTests) {
        Write-Host "  [~] $($test.Category) - $($test.TestName) ($($test.Points)/$($test.MaxPoints))" -ForegroundColor Yellow
        Write-Host "      Details: $($test.Details)" -ForegroundColor Gray
    }
    Write-Host ""
}

$reportPath = "grading-report-$(Get-Date -Format 'yyyyMMdd-HHmmss').json"
$report = @{
    TotalScore = $global:totalPoints
    MaxScore = $global:maxPoints
    Percentage = $finalPercentage
    Grade = $grade
    Timestamp = (Get-Date).ToString("yyyy-MM-dd HH:mm:ss")
    CategoryScores = $global:categoryScores
    DetailedResults = $global:testResults
}

$report | ConvertTo-Json -Depth 10 | Out-File $reportPath
Write-Host "Detailed report saved to: $reportPath" -ForegroundColor Gray
Write-Host ""
Write-Host "Grading completed!" -ForegroundColor Cyan
