# Custom Module - Translation Controller

## Overview
This module provides a memory-optimized translation controller for Craft CMS that can handle large numbers of entries without running into memory exhaustion issues.

## Memory Optimization Features

The controller has been optimized to:
- Process entries in configurable batches (default: 100)
- Clear memory after each batch
- Force garbage collection between batches
- Clear Craft's element cache after each batch
- Unset variables immediately after use
- Provide detailed memory usage monitoring

## Smart Field Validation

The controller automatically skips translation for non-translatable content:

### **Automatically Skipped:**
- **URLs**: `https://example.com`, `mailto:user@domain.com`, `tel:+1234567890`
- **Email addresses**: `user@domain.com`
- **Phone numbers**: `+1 (555) 123-4567`, `555-1234`
- **Temporary values**: Any field containing `__temp`
- **File paths**: `images/photo.jpg`, `documents/file.pdf`
- **Social media**: `@username`, `#hashtag`
- **Numbers only**: `123`, `42.5`
- **Punctuation only**: `!@#$%`
- **Too short**: Values less than 3 characters

### **Examples of Skipped Fields:**
```
- Skipping field: phone (URL/email/phone/temp value).
- Skipping field: website (URL/email/phone/temp value).
- Skipping field: email (URL/email/phone/temp value).
- Skipping field: file_path (URL/email/phone/temp value).
```

## Available Commands

### 1. Translate All Entries
```bash
# Process all entries with default batch size (100)
./craft custommodule/translate/translate-all

# Process with custom batch size (e.g., 50)
./craft custommodule/translate/translate-all 50

# Process with very small batch size for low memory environments
./craft custommodule/translate/translate-all 25
```

### 2. Resume Translation from Specific Point
```bash
# Resume from entry 4000 (useful if process was interrupted)
./craft custommodule/translate/resume 4000

# Resume from entry 4000 with custom batch size
./craft custommodule/translate/resume 4000 50
```

### 3. Check Translation Status
```bash
# Check current progress and memory usage
./craft custommodule/translate/status
```

### 4. Memory Optimization
```bash
# Run memory cleanup and optimization
./craft custommodule/translate/optimize
```

### 5. Test Field Validation
```bash
# Test the smart field validation logic
./craft custommodule/translate/test-validation
```

This command shows examples of what content would be skipped vs. translated.

## Memory Management

### Batch Processing
- Default batch size: 100 entries
- Configurable range: 1-500 entries
- Memory is cleared after each batch
- Garbage collection is forced between batches

### Memory Monitoring
The controller provides real-time memory information:
- Current memory usage
- Peak memory usage
- Memory change per batch
- Memory usage before and after optimization

### Recommended Batch Sizes
- **Low memory servers (256MB)**: 25-50 entries
- **Medium memory servers (512MB)**: 50-100 entries
- **High memory servers (1GB+)**: 100-200 entries

## Troubleshooting

### Memory Exhaustion
If you still encounter memory issues:
1. Reduce batch size: `./craft custommodule/translate/translate-all 25`
2. Run optimization: `./craft custommodule/translate/optimize`
3. Check memory status: `./craft custommodule/translate/status`

### Resuming Interrupted Process
If the translation process is interrupted:
1. Note the last processed entry number
2. Use resume command: `./craft custommodule/translate/resume [last_entry_number]`
3. The process will continue from where it left off

### Performance Tips
- Run during low-traffic periods
- Monitor server resources during execution
- Use smaller batch sizes on shared hosting
- Consider running in background with `nohup` if supported

## Example Output

```
Starting translations from site 2 to site 3
Batch size: 100 entries
Total entries to process: 6000

--- Processing batch 1 (entries 1-100) ---
[1/6000] Sample Entry (ID 123)
  ✓ Enqueued 3 translation job(s).
[2/6000] Another Entry (ID 124)
  - Skipping field: phone (URL/email/phone/temp value).
  - Skipping field: website (URL/email/phone/temp value).
  ✓ Enqueued 1 translation job(s).

--- Batch 1 completed ---
Current memory: 45.2 MB
Peak memory: 67.8 MB
Memory change: +2.1 MB
```

## Configuration

The controller uses these constants:
- `SOURCE_SITE_ID`: Slovenian site (default: 2)
- `TARGET_SITE_ID`: English site (default: 3)

These can be modified in the controller file if needed.
