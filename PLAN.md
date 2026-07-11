# Cleanup Plan: index.php / stylesheet.css / index.html

## Context
After removing the dead `google_start`/`google_stop` block (commit 0d0fc59),
a follow-up read-only sweep of the whole repo (not just index.php) turned up
more dead code/content. Scope was confirmed with the repo owner — selected:
dead commented-out PHP include, per-publication placeholder comments, unused
CSS rules, **and** (via free-text) deleting `index.html` entirely. Orphaned
image files in `images/` were presented as an option but **not** selected —
`images/` is left untouched in this pass.

## Findings

1. **`stylesheet.css`** — two rules are defined but never applied anywhere
   in `index.php` (confirmed via grep, 0 usages of `class="fade"` or
   `class="highlight"`/`<span class="highlight"`):
   - `.fade { ... }` (lines ~123-127)
   - `span.highlight { ... }` (lines ~129-131) — the only remaining
     reference to "highlight" in index.php is itself a dead comment
     (`<!-- highlighting: bgcolor="#ffffd0">-->` at line 799), being removed
     in this same pass.

2. **`index.php` line 62** — dead commented-out PHP include:
   `<!--?php  $userid="bIaHh6gAAAAJ"; include 'total_citations.php'; ?> &nbsp/&nbsp -->`
   Same category as the already-removed google block.

3. **`index.php` — scattered dead comments across publication entries.**
   Categorized rather than enumerated line-by-line (each verified by reading
   surrounding context):
   - **Header bio links (lines ~59-72):** commented-out old MaD Lab
     affiliation link, jonbarron template's own `data/No-CV.pdf` /
     `data/JonBarron-bio.txt` placeholders, dead Facebook link, two
     superseded old blog URLs (the live one is already on line 73).
   - **Recurring "bibtex" placeholder** `<!--<a href="data/cvpr2020.bib">bibtex</a> /-->`
     — appears **14 times** (verified via `grep -c 'cvpr2020.bib' index.php`)
     at lines 243, 520, 565, 610, 695, 737, 789, 835, 874, 911, 951, 990,
     1027, 1064. `data/cvpr2020.bib` doesn't exist in this repo — pure
     inherited jonbarron-template boilerplate never customized per paper.
     Note line 874 has the closing `-->` on its own line as part of a
     multi-line comment (see next bullet) — remove that whole comment block,
     not just the bibtex line in isolation.
   - **Recurring "demo video" / "project" placeholder pairs** e.g.
     `<!--<a href="https://www.youtube.com/watch?v=MCpC2i-ZbDs">demo video</a> /-->`,
     `<!--<a href="https://iis.fraunhofer.de/tooltracking">project</a> / -->`
     at lines ~242-244, 564-566, 609-611, 692-697 — identical boilerplate
     repeated across several entries, never filled in.
   - **Copy-paste artifacts** (comments referencing a *different* paper than
     the entry they sit in — confirmed by cross-referencing URLs elsewhere
     in the file): line 122 (metamers' arxiv/DOI link, wrongly left in the
     "rag" entry — same DOI appears correctly at line 351, same arxiv id at
     line 159); line 738 (`github.com/crispchris/iale` link commented out
     inside the unrelated ISMAR entry); lines 873-874 (a ViPR video link
     `youtu.be/ZCTxPJPCfF0?t=20577`, identical to the real one used at line
     831, wrongly duplicated + commented into the GRAPP entry, bundled with
     another bibtex placeholder).
   - **Misc one-off cruft:** inline `<!-- bgcolor="#ffffd0">-->` annotations
     at lines 665 and 707 (documenting an attribute that isn't applied
     there); truncated old venue-text fragment `<!-- 23 (2022) 1-29</em> -->`
     at lines 562 and 607; the commented papertitle-wrapping link at lines
     819/821 (`<!--<a href="...fourfeat/index.html">-->` /
     `<!--</a>-->`, another unfilled jonbarron template link); the inherited
     "Konishi" joke/attribution comment near the footer (~line 1241) —
     leftover from the forked template, irrelevant to this site.
   - **Explicitly NOT removing:** the commented-out
     `<?php $paperid="14918946306010996143"; include 'citation.php'; ?>` at
     line 735 (ISMAR poster entry) — this reads as a deliberate per-paper
     choice to suppress a citation count, not template cruft, so leave it.
     Also keep the two top-of-file attribution/documentation comments (line
     15 Google-tag marker, line 28 PHP-magic attribution) — these add real
     context, unlike the rest.

4. **`index.html`** — delete the file entirely (explicit confirmation).
   Verified nothing references it: no href/link anywhere in `index.php`,
   `stylesheet.css`, or `blog/index.html` points to it (only false-positive
   grep hit was an unrelated external URL containing the literal substring
   "index.html", inside one of the dead comments being removed in this same
   pass). `blog/index.html` is a separate, unrelated file and is NOT part of
   this deletion.

5. **`CLAUDE.md`** — update to match, since it currently documents
   `index.html` as a file to keep/not-delete-without-asking (now moot) and
   documents `total_citations.php`'s commented include (the include is being
   removed, though the file `total_citations.php` itself stays — deleting
   just the dead reference to it in index.php, not the file).

## Execution steps

1. Edit `stylesheet.css`: remove the `.fade` and `span.highlight` rule
   blocks.
2. Edit `index.php`:
   - Remove line 62 (dead total_citations.php include comment).
   - Remove the header-bio dead link comments (lines ~59-61, 66, 71-72).
   - Remove all 14 `cvpr2020.bib` bibtex placeholder comments (and their
     enclosing multi-line comment where they're bundled with other dead
     content, e.g. 873-874).
   - Remove the recurring demo-video/project placeholder comment pairs.
   - Remove the four copy-paste-artifact comments (122, 738, 873-874 already
     counted above, 819/821).
   - Remove the misc one-offs: 665, 707 inline bgcolor annotations; 562, 607
     truncated venue fragments; the Konishi footer comment.
   - Leave line 735's commented citation.php include and the two top-of-file
     attribution comments untouched.
   - After edits, re-grep for `<!--` in index.php to confirm only the
     intended keepers remain, and re-run the used/unused JS function check
     from the prior round to make sure no function definitions got
     accidentally orphaned by comment removal.
3. Delete `index.html` (`git rm index.html`).
4. Update `CLAUDE.md`:
   - Remove/rewrite the `index.html` bullet (file no longer exists).
   - Update the `total_citations.php` bullet to reflect that even the
     commented-out include reference in index.php is now gone.
5. Run `php -l index.php` (and the other three `.php` files, unaffected but
   cheap to re-verify) to confirm no syntax breakage.
6. Spin up `php -S localhost:PORT`, `curl` the page, and spot-check a couple
   of the edited sections (header bio row, a couple of publication entries)
   render with the same visible content as before (comments never rendered,
   so visible output should be byte-for-byte identical except for the
   removed `index.html`/CSS surface).
7. Commit in two logical commits: one for the index.php/stylesheet.css
   dead-comment sweep, one for the index.html deletion + CLAUDE.md update.
   Push to `claude/claude-md-docs-e2ga3p`.

## Verification
- `php -l` on all four `.php` files.
- Local `php -S` smoke test: page returns 200, key section markers
  (`Research`, `Publications`/paper titles, `Patents`, `Teaching`) still
  present.
- `grep -c "<!--" index.php` before/after to confirm the expected count
  dropped and only the ~3 intended keepers (Google tag marker, PHP-magic
  attribution, the one deliberate commented citation include) remain.
- `ls index.html` fails (file gone); `git status` shows it as deleted, not
  just untracked.
