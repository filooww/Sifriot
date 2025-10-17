# Story Dependencies

**Visual dependency map:**

```
1.1 (Models) ───┬─→ 1.3 (Access) ─→ 1.4 (Search) ─→ 1.5 (Filters) ─→ 1.10 (Detail)
                │                                                              │
                └─→ 1.2 (Languages) ──────────────────────────────────────────┤
                                                                               │
1.6 (File Reg) ─→ 1.6A (Folder Browser) ─┬─→ 1.7 (Bulk Scan) ─→ 1.8 (Extract)┤
                                          │                                    │
                                          ├─→ 1.6C (Folder Rules) ────────────┤
                                          │                                    │
                                          └─→ 1.6B (File Sync) ───────────────┤
                                                                               │
1.9 (Custom Fields) ────────────────────────────────────────────────────────→┤
                                                                               │
                                                                               ├─→ 1.11 (Views)
                                                                               ├─→ 1.12 (Likes)
                                                                               ├─→ 1.13 (Downloads)
                                                                               ├─→ 1.14 (Comments)
                                                                               ├─→ 1.15 (Bookmarks)
                                                                               ├─→ 1.16 (Workflow)
                                                                               │
                                                                               ├─→ 1.17 (Author Pages)
                                                                               │
1.11-1.17 ──────────────────────────────────────────────────────────────────────→ 1.18 (Dashboard)
                                                                               │
1.8 (Extraction) ───────────────────────────────────────────────────────────────→ 1.19 (Extraction Rules)
                                                                               │
1.11-1.15 ──────────────────────────────────────────────────────────────────────→ 1.20 (User Profile)
```

**Story Sequencing (Updated):**
1. Foundation: 1.1 → 1.2 → 1.3
2. Core Search/Filter: 1.4 → 1.5
3. File Management: 1.6 → 1.6A → 1.6C, 1.6B (parallel) → 1.7 → 1.8
4. Advanced Features: 1.9 → 1.10
5. Engagement: 1.11-1.16 (can be parallel after 1.10)
6. Polish: 1.17 → 1.18 → 1.19 → 1.20

**Note:** Stories 1.6B (File Sync) and 1.6C (Folder Rules) can be developed in parallel after 1.6A. Story 1.6B is optional for MVP and can be deferred to post-MVP if needed.

---
