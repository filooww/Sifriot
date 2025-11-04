# Bulk Scanning with Metadata Extraction - Complete Guide

## 📋 Quick Overview

You now have **three ways** to extract metadata:

1. **Automatic during bulk scan** - Files extracted as they're registered
2. **Bulk command for existing files** - Extract all unextracted files at once
3. **Manual per-file** - Review and extract individual files in UI

---

## 🚀 Method 1: Automatic Extraction During Bulk Scan

This is the **easiest and recommended approach** for new files.

### **Setup (One-Time)**

1. Ensure extraction is enabled in `.env`:
```env
METADATA_EXTRACTION_ENABLED=true
METADATA_EXTRACTION_TIMEOUT=30
METADATA_EXTRACTION_RETRIES=3
```

2. Start queue worker (in a separate terminal):
```bash
docker compose exec web php artisan queue:work -v
```

### **Usage**

1. Navigate to `/admin/files`
2. Click **"📁 Browse & Register Files"** tab
3. Scroll down to **"Bulk Folder Scan"** section
4. Enter folder path: `/books` or `/magazines/2024`
5. Click **"Start Scan"**
6. Watch progress:
   - "Files discovered: 500"
   - "Files registered: 25/500"
   - In another terminal: Queue worker shows extraction jobs

7. When scan completes:
   - Files registered ✅
   - Metadata extraction automatically queued ✅

8. Click **"📋 Metadata Review"** tab
9. Watch as metadata appears (status: pending → processed)
10. Confirm or edit metadata for each file

### **Example Timeline: 100 PDFs**

```
T+0s:    Start bulk scan
T+2s:    100 files discovered
T+3s:    First 10 files registered + 10 extraction jobs queued
T+6s:    Next 15 files registered + 15 extraction jobs queued
T+15s:   All 100 files registered + 100 extraction jobs queued
T+20s:   First 5 extractions complete
T+45s:   All 100 extractions complete
T+50s:   Admin reviews 100 items in Metadata Review tab
```

---

## 🔧 Method 2: Extract Metadata from Already-Scanned Files

Use this when you have **files already registered** but without metadata.

### **Option A: Extract All Unextracted Files**

```bash
docker compose exec web php artisan metadata:extract-all
```

**Output:**
```
🔍 Processing 500 files for metadata extraction...
████████████████████████████████████████████████████████ 500/500
═══════════════════════════════════════════
📊 Metadata Extraction Summary
═══════════════════════════════════════════
✅ Queued: 500 files
═══════════════════════════════════════════

⏳ Extraction in progress. Monitor with:
   php artisan queue:work -v

📋 View results in: /admin/files → Metadata Review tab
```

**Then monitor with:**
```bash
docker compose exec web php artisan queue:work -v
```

---

### **Option B: Extract Only Books (Content Type 1)**

```bash
docker compose exec web php artisan metadata:extract-all --content-type=1
```

This only extracts files marked as "Books" and applies ISBN/DOI extraction patterns.

---

### **Option C: Extract First 100 Files**

```bash
docker compose exec web php artisan metadata:extract-all --limit=100
```

Useful for testing before processing entire library.

---

### **Option D: Force Re-extract Everything**

```bash
docker compose exec web php artisan metadata:extract-all --force
```

Even files that already have metadata will be re-extracted. Useful for:
- Upgrading extraction logic
- Testing new extraction patterns
- Fixing confidence scores

---

### **Option E: Large Batch with Custom Chunk Size**

```bash
docker compose exec web php artisan metadata:extract-all \
  --limit=1000 \
  --chunk-size=100
```

Processes 1000 files in chunks of 100 to prevent memory exhaustion.

---

## 📊 Complete Command Reference

### **Extract Everything**
```bash
php artisan metadata:extract-all
```

### **Extract with Filters**
```bash
php artisan metadata:extract-all --limit=500 --content-type=1
```

### **Re-extract All**
```bash
php artisan metadata:extract-all --force
```

### **Extract in Smaller Chunks (Memory Efficient)**
```bash
php artisan metadata:extract-all --chunk-size=50
```

### **Extract Magazine Files Only**
```bash
php artisan metadata:extract-all --content-type=2
```

### **Real-Time Options**

| Option | Value | Purpose |
|--------|-------|---------|
| `--limit=N` | Integer | Stop after N files |
| `--content-type=ID` | 1,2,3,4 | Filter by content type |
| `--chunk-size=N` | Integer (default: 50) | Batch processing size |
| `--force` | Boolean flag | Re-extract everything |

---

## 🎯 Method 3: Manual Per-File Re-extraction

For files needing re-extraction in the UI.

### **Steps**

1. Go to `/admin/files` → **"📋 Metadata Review"** tab
2. Find file with status "failed" or "rejected"
3. Click **"Re-extract"** button on that row
4. New extraction job dispatched
5. Watch status update in real-time

---

## 📈 Performance & Monitoring

### **Check Progress in Terminal**

```bash
# Watch extraction jobs complete
docker compose exec web php artisan queue:work -v
```

Example output:
```
[2025-10-28 14:30:15] Processing: App\Jobs\ExtractMetadataFromFile
[2025-10-28 14:30:20] Finished:  App\Jobs\ExtractMetadataFromFile
[2025-10-28 14:30:21] Processing: App\Jobs\ExtractMetadataFromFile
[2025-10-28 14:30:35] Finished:  App\Jobs\ExtractMetadataFromFile
```

### **Check Progress in Database**

```bash
docker compose exec db mysql -u dbuser -pdbpass db_manager -e "
SELECT status, COUNT(*) as count
FROM file_metadatas
GROUP BY status;
"
```

Example output:
```
status      | count
------------|-------
pending     | 50
processed   | 450
confirmed   | 0
rejected    | 0
failed      | 0
```

### **Monitor Queue Length**

```bash
docker compose exec db mysql -u dbuser -pdbpass db_manager -e "
SELECT COUNT(*) as 'Jobs in Queue'
FROM jobs;
"
```

---

## ⚡ Optimization Tips

### **For 100-500 Files**

```bash
# Just use default settings
php artisan metadata:extract-all
# One queue worker handles it fine
docker compose exec web php artisan queue:work
```

### **For 500-2000 Files**

```bash
# Use larger chunks
php artisan metadata:extract-all --chunk-size=100

# Start 2-3 queue workers
docker compose exec -d web php artisan queue:work
docker compose exec -d web php artisan queue:work
```

### **For 2000+ Files**

```bash
# Use even larger chunks and multiple workers
php artisan metadata:extract-all --chunk-size=200

# Start 5+ queue workers
for i in {1..5}; do
    docker compose exec -d web php artisan queue:work
done

# Monitor in separate terminal
watch 'docker compose exec db mysql -u dbuser -pdbpass db_manager -e "SELECT COUNT(*) FROM jobs;"'
```

### **Memory Efficiency**

Default chunk size is **50 files** per batch. For memory-constrained systems:

```bash
php artisan metadata:extract-all --chunk-size=25
```

For systems with plenty of RAM:

```bash
php artisan metadata:extract-all --chunk-size=200
```

---

## 📊 Expected Performance

| Batch Size | Single Worker | 3 Workers |
|-----------|---------------|-----------|
| 50 files | 45-90 sec | 20-30 sec |
| 100 files | 90-180 sec | 40-60 sec |
| 500 files | 7-15 min | 2-5 min |
| 1000 files | 15-30 min | 5-10 min |

*Times depend on: file format (PDFs slower), file size, system resources*

---

## 🐛 Troubleshooting

### **Command Says "No files to process"**

**Cause:** All files already have metadata extracted

**Solution:** Use `--force` to re-extract:
```bash
php artisan metadata:extract-all --force
```

---

### **Extraction Jobs Not Processing**

**Cause:** Queue worker not running

**Solution:** Start queue worker:
```bash
docker compose exec web php artisan queue:work -v
```

---

### **Memory Exhausted Error**

**Cause:** Chunk size too large

**Solution:** Reduce chunk size:
```bash
php artisan metadata:extract-all --chunk-size=25
```

---

### **Extraction Timeout Errors**

**Cause:** Large PDFs taking >30 seconds

**Solution:** Increase timeout in `.env`:
```env
METADATA_EXTRACTION_TIMEOUT=60
```

Then restart queue worker.

---

### **Files Appear in Review Tab but Status Stuck as "pending"**

**Cause:** Queue worker stopped or extraction failed

**Solution:**
1. Check queue worker status
2. Look for errors in logs:
```bash
docker compose exec web tail -f storage/logs/laravel.log | grep metadata
```

---

## 🎯 Typical Workflows

### **Workflow A: New Folder of 200 PDFs**

```
1. File → Admin → /admin/files
2. Click "📁 Browse & Register Files"
3. Enter folder path, start bulk scan
4. System automatically:
   - Discovers 200 files
   - Registers all 200
   - Queues 200 metadata extractions
5. Admin clicks "📋 Metadata Review"
6. Sees 200 files with extracted metadata
7. Reviews and confirms metadata (can bulk confirm)
8. Done! All 200 files registered with metadata
```

**Time:** ~5-10 minutes total

---

### **Workflow B: Existing 500 Files Need Metadata**

```
1. Admin opens terminal
2. Runs: php artisan metadata:extract-all
3. Command queues 500 extraction jobs
4. In separate terminal: php artisan queue:work -v
5. Queue processes 500 jobs (15-20 min)
6. Admin goes to /admin/files → Metadata Review
7. All 500 files show extracted metadata
8. Confirms/rejects as needed
9. Done! All 500 files have metadata
```

**Time:** ~20-30 minutes total

---

### **Workflow C: Re-extract Failed Files**

```
1. Some files failed extraction
2. Showing in Metadata Review with status "failed"
3. Admin selects failed files and clicks "Re-extract"
4. New extraction jobs dispatched for failed files
5. Queue worker processes re-extraction
6. Files either succeed or show new error
7. Admin can manually edit if needed
```

**Time:** Depends on file count, typically <5 minutes

---

## 📋 Configuration Checklist

- [ ] `.env` has `METADATA_EXTRACTION_ENABLED=true`
- [ ] Queue is configured (database, redis, or sync)
- [ ] Queue worker is running (`php artisan queue:work`)
- [ ] Database tables exist (`php artisan migrate`)
- [ ] Extraction rules seeded (`php artisan db:seed --class=ExtractionRulesSeeder`)
- [ ] File permissions allow reading library files
- [ ] Disk space available for queue table growth

---

## 🎓 Learning Path

1. **Start here:** Try Method 1 with a small 10-file folder
2. **Then:** Try Method 2 with `--limit=50`
3. **Finally:** Run full extraction with optimizations

---

## 📞 Support

**Check logs:**
```bash
docker compose exec web tail -f storage/logs/laravel.log
```

**Check queue health:**
```bash
docker compose exec web php artisan queue:failed
```

**Clear failed jobs:**
```bash
docker compose exec web php artisan queue:retry
```

