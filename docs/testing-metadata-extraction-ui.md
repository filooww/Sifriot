# Testing Metadata Extraction Features in the UI

## Access Points

### 1. **File Registration/Upload** (Triggers Extraction)
**URL:** `http://localhost/admin/files/register`
**Route Name:** `admin.files.register`

**What Happens:**
- Admin registers a file from external server OR uploads a file
- System automatically triggers metadata extraction job
- File is queued for background processing
- User sees confirmation message: "File registered successfully. Metadata extraction started..."

**Steps to Test:**
1. Go to `/admin/files/register`
2. Select a content type (Books, Articles, Magazines, etc.)
3. Either:
   - **Register Existing File:** Browse server files and select PDF/EPUB/TXT/DOC/DOCX/FB2/DJVU
   - **Upload New File:** Upload any supported document format
4. Click "Register" or "Upload"
5. Verify success message appears
6. **Queue should process the job in background** (watch `php artisan queue:work`)

---

### 2. **Metadata Review Queue Dashboard** (Main Feature)
**URL:** `http://localhost/admin/metadata-review`
**Route Name:** `admin.metadata-review`

**What You'll See:**
- **Statistics cards** showing counts:
  - ⏳ Pending (extraction in progress)
  - 🔄 Processing (completed, waiting for review)
  - ✅ Confirmed (admin approved)
  - ❌ Failed (extraction error)
  - 🚫 Rejected (admin manually entered)

- **Filter Options:**
  - Status (All, Pending, Processing, Confirmed, Failed, Rejected)
  - Format (All, PDF, EPUB, TXT, DOC, DOCX, FB2, DJVU)
  - Date Range (All Time, Last 24h, Last 7 days, Last 30 days)
  - Sort By (Date, Filename, Status)

- **Table Listing:**
  - Filename with extracted title preview
  - Format badge (PDF, EPUB, etc.)
  - Status badge with icon
  - Extraction date
  - Action buttons

- **Bulk Actions** (select multiple items):
  - ✅ Confirm All Selected
  - 🚫 Reject All Selected
  - 🔄 Re-extract Selected

**Steps to Test Dashboard:**
1. Register/upload 3-5 files with different formats
2. Go to `/admin/metadata-review`
3. Wait for queue to process (or run `php artisan queue:work`)
4. **Test Filters:**
   - Filter by status "Processing" (ready for review)
   - Filter by format "PDF"
   - Change date range
5. **Test Sorting:**
   - Click column headers to sort by Date, Filename, Status
6. **Test Search:**
   - Look for files in the list

---

### 3. **Metadata Review Form** (Individual Review)
**URL:** `http://localhost/admin/metadata-review/{id}` (opened from dashboard)
**Route Name:** `admin.metadata-review.show`

**What You'll See:**
- **File Information Header:**
  - Filename
  - Format badge (PDF, EPUB, etc.)
  - Status badge (Processing, Confirmed, Failed, Pending)

- **Extraction Details** (collapsible):
  - Extractor used (PDFMetadataExtractor, EPUBMetadataExtractor, etc.)
  - Extraction timestamp
  - Confirmation details (if confirmed)

- **Form Fields** with confidence indicators:
  - **Title** - with confidence % (Green/Blue/Yellow/Red)
  - **Authors** - editable repeating field, add/remove buttons
  - **Publication Year** - numeric input
  - **Publisher** - text input
  - **ISBN** - with format validation
  - **DOI** - with format validation

- **Action Buttons:**
  - ✅ **Confirm Extraction** (if processing) - Accept extracted data
  - ❌ **Reject & Edit** (if processing) - Reject and show manual form
  - 💾 **Save Manual Entry** (if rejected/failed) - Manually enter metadata

**Steps to Test Metadata Review:**

#### Scenario A: Accept Extraction
1. Register a PDF/EPUB file
2. Wait for extraction to complete
3. Go to `/admin/metadata-review`
4. Find file with status "📋 Ready for Review"
5. Click "Review" button
6. Review extracted metadata and confidence scores
7. Click "✅ Confirm Extraction"
8. Verify status changes to "✅ Confirmed"
9. Verify "confirmed_at" timestamp appears

#### Scenario B: Edit & Confirm
1. Open metadata review form (status: processing)
2. Review extracted data
3. Edit fields (change title, add/remove authors)
4. Change publication year
5. Click "✅ Confirm Extraction"
6. Verify edits were saved with confirmation

#### Scenario C: Reject & Manual Entry
1. Open metadata review form (status: processing)
2. Click "❌ Reject & Edit"
3. Form clears and switches to manual mode
4. Manually fill in:
   - Title
   - Authors (add multiple if needed)
   - Publication Year
   - Publisher
   - ISBN
   - DOI
5. Click "💾 Save Manual Entry"
6. Verify status changes to "✅ Confirmed"
7. Verify "extraction_method" is "manual_entry"

#### Scenario D: Re-extract Failed
1. Open metadata review form (status: failed)
2. See error message
3. From dashboard, select failed item
4. Click "🔄 Re-extract Selected"
5. Job requeued for processing
6. Wait for queue worker
7. Refresh dashboard
8. Verify status changes to "📋 Ready for Review"

---

## Testing Workflow (Complete End-to-End)

### Step 1: Prepare Test Files
Create test files or use existing ones:
- `test-book.pdf` - Book in PDF format
- `test-article.epub` - Article in EPUB format
- `test-document.docx` - Document in DOCX format

### Step 2: Start Queue Worker
```bash
# In terminal 1
php artisan queue:work

# Or with high verbosity to see job details
php artisan queue:work -v
```

### Step 3: Register Files
```
1. Go to http://localhost/admin/files/register
2. Register test-book.pdf (format: Books)
3. Upload test-article.epub (format: Articles)
4. Upload test-document.docx (format: Magazines)
```

### Step 4: Monitor Extraction
In the queue worker terminal, you should see:
```
[2025-10-26 22:00:00] Processing: App\Jobs\ExtractMetadataFromFile
[2025-10-26 22:00:02] Finished:  App\Jobs\ExtractMetadataFromFile
```

### Step 5: Review Results
```
1. Go to http://localhost/admin/metadata-review
2. Verify 3 items appear in the list
3. Verify statistics update (3 "Processing" or "Confirmed")
4. Verify status badges show correct icons
```

### Step 6: Test Each Review Form
For each file:
```
1. Click "Review" button
2. Check confidence scores displayed
3. Verify all fields pre-filled from extraction
4. For 1st file: Confirm extraction ✅
5. For 2nd file: Edit title and confirm ✅
6. For 3rd file: Reject and manually enter metadata 💾
```

### Step 7: Verify Database
Check database records:
```sql
-- View extraction results
SELECT id, file_name, status, extraction_method, extracted_at
FROM file_metadatas;

-- View confidence scores
SELECT id, file_name, confidence_scores
FROM file_metadatas
WHERE status = 'confirmed';

-- View extraction rules
SELECT * FROM extraction_rules WHERE enabled = true;
```

---

## Expected Behaviors

### Success Scenarios ✅
1. **File Registration:**
   - [x] File registered successfully
   - [x] Message shows "Metadata extraction started..."
   - [x] Queue job created

2. **Extraction Processing:**
   - [x] Job processes within 30 seconds
   - [x] FileMetadata record created
   - [x] Status changes from "pending" → "processed"
   - [x] Extracted data stored in JSON
   - [x] Confidence scores calculated

3. **Admin Review:**
   - [x] Dashboard lists all pending/processing extractions
   - [x] Confidence scores display with color indicators
   - [x] Form fields pre-filled with extracted values
   - [x] Edits are accepted without validation errors

4. **Confirmation:**
   - [x] Status changes to "confirmed"
   - [x] Timestamp recorded in "confirmed_at"
   - [x] Manual entries recorded with 100% confidence
   - [x] Event fired for listeners

### Error Scenarios 🔄
1. **Failed Extraction:**
   - [x] Status changes to "failed"
   - [x] Error message recorded
   - [x] Admin can see error details
   - [x] Admin can retry extraction

2. **Invalid Input:**
   - [x] Form validation prevents submission without title
   - [x] ISBN format validated (if provided)
   - [x] DOI format validated (if provided)
   - [x] Year must be valid (1000-current)

3. **Rejected Extraction:**
   - [x] Status changes to "rejected"
   - [x] Timestamp recorded in "rejected_at"
   - [x] Manual entry form becomes available

---

## Confidence Score Display Guide

### What the Colors Mean

| Color | Confidence | Meaning | Action |
|-------|------------|---------|--------|
| 🟢 Green | 90-100% | Very Confident | Accept as-is |
| 🔵 Blue | 70-89% | Confident | Review, likely correct |
| 🟡 Yellow | 50-69% | Moderate | Review carefully |
| 🔴 Red | <50% | Low | Verify or manually enter |

### Example Display
```
Title: "The Great Gatsby" [95% confident] 🟢
Authors: ["F. Scott Fitzgerald"] [92% confident] 🟢
Publication Year: [85% confident] 🔵
Publisher: "Charles Scribner's Sons" [78% confident] 🔵
ISBN: "978-0-7432-7356-5" [95% confident] 🟢
DOI: [0% confident] ❌ (not provided)
```

---

## Database Logging

### Monitor Extraction in Logs
```bash
# Watch the folder_scan log in real-time
tail -f storage/logs/folder_scan.log

# Look for entries like:
# [2025-10-26 22:00:00] INFO: Starting metadata extraction
# [2025-10-26 22:00:02] INFO: Metadata extraction completed successfully
# [2025-10-26 22:00:03] INFO: FileMetadata record created
```

---

## Troubleshooting

### Queue Not Processing Jobs
```bash
# Check if queue worker is running
ps aux | grep queue

# Start queue worker with verbosity
php artisan queue:work -v

# Run job synchronously for testing
# Set QUEUE_CONNECTION=sync in .env
```

### Extraction Status Stuck on "Pending"
```bash
# Check job table in database
SELECT * FROM jobs;

# Check failed jobs
SELECT * FROM failed_jobs;

# Clear failed jobs and retry
php artisan queue:retry all
```

### Form Not Pre-filling
```bash
# Check FileMetadata record in database
SELECT * FROM file_metadatas WHERE file_id = 'test-123';

# Verify extracted_data JSON is valid
SELECT JSON_VALID(extracted_data) FROM file_metadatas;
```

### Confidence Scores Not Displaying
```bash
# Check confidence_scores column
SELECT id, confidence_scores FROM file_metadatas LIMIT 1;

# Should return JSON like:
# {"title": 0.95, "authors": 0.92, "publication_year": 0.85, ...}
```

---

## Browser Developer Tools Tips

### Check Network Requests
1. Open DevTools (F12)
2. Go to Network tab
3. Perform actions:
   - Register file → Watch POST request
   - Review metadata → Watch Livewire updates
   - Confirm extraction → Watch PUT request

### Check Console for Errors
1. Open DevTools Console tab
2. Look for JavaScript errors
3. Check Livewire component logs

### Test Form Validation
1. Open DevTools Console
2. Open metadata review form
3. Try submitting with empty title
4. Should see validation error

---

## Performance Testing

### Test Extraction Speed
1. Register 5-10 files of different formats
2. Time how long extraction takes per format
3. Expected times:
   - PDF: 1-3 seconds
   - EPUB: 0.5-2 seconds
   - DOCX: 0.5-2 seconds
   - TXT: <0.5 seconds
   - FB2: 0.5-2 seconds
4. Total should complete within 30-second timeout

### Test Dashboard Performance
1. Create 100+ FileMetadata records
2. Load `/admin/metadata-review`
3. Test filtering/sorting speed
4. Should load within 2 seconds

---

## Production Checklist

Before going live, verify:
- [ ] Queue worker configured and running
- [ ] Database migrations applied
- [ ] Seeder run for default extraction rules
- [ ] Tests pass: `php artisan test`
- [ ] All 7 formats extract metadata correctly
- [ ] Confidence scores display properly
- [ ] Admin can confirm/reject/edit
- [ ] Error handling graceful
- [ ] Logs capture all operations
- [ ] No JavaScript errors in console
- [ ] Form validation working
- [ ] Bulk actions functional

---

**Happy Testing! 🎉**

For issues or questions, check `docs/metadata-extraction.md` for comprehensive system documentation.
