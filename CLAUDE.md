# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

This is Christoffer Löffler's personal academic homepage: a CV/publications
page with static HTML plus a thin layer of PHP that fetches live citation
counts from Google Scholar. There is no framework, no build step, and no
JavaScript beyond small inline hover-effect snippets. The page layout is
forked from the [jonbarron_website](https://github.com/jonbarron/jonbarron_website)
template (credited in the footer of `index.php`).

## File map

- `index.php` — **the real, live homepage** (bio, Research, Publications,
  Patents, Teaching sections). Almost entirely hand-written HTML tables with
  a `<?php ... ?>` include sprinkled into each publication entry to print a
  live citation count.
- `index.html` — a static duplicate that is **stale/legacy**: last edited
  2022 vs. `index.php`'s ongoing updates, describes the author as "PhD
  student" instead of "profesor asociado", and has far fewer publications.
  Don't treat it as a second source of truth — if a request is "update the
  homepage" it almost certainly means `index.php`. Don't delete it without
  checking with the user first, since its role (still served somewhere? pure
  cruft?) isn't determinable from the repo alone.
- `curl.php` — included once near the top of `index.php` with `$lang`,
  `$userid` (Scholar profile id), `$pagesize` already set. cURLs the Google
  Scholar "list works" page into `$GScontent`. Must be included *before* any
  use of `citation.php`/`total_citations.php`, since they both read
  `$GScontent`.
- `citation.php` — included inline next to a specific publication, with
  `$paperid` (that paper's Scholar cluster id) set immediately before the
  include. Regex-scrapes `$GScontent` for that paper's citation count and
  echoes a "N citation(s)" link.
- `total_citations.php` — same idea but for the aggregate citation count
  across the whole profile; currently commented out and unused in
  `index.php`.
- Both scrapers are fragile, hand-rolled regexes against Google Scholar's
  current HTML. If Scholar changes markup, they degrade silently (print
  nothing, or literal `e1`/`e2` placeholders) rather than erroring loudly.
- `stylesheet.css` — all styling. Defines non-semantic custom tags used
  throughout the HTML as styling hooks instead of real semantic
  elements/classes: `<heading>`, `<papertitle>`, `<name>`. Also defines
  `.one`/`.two` (the absolutely-positioned image pair used for the hover
  swap effect) and `span.highlight` / the `bgcolor="#ffffd0"` rows used to
  visually group certain publications. Reuse these rather than introducing
  new divs/classes for similar effects.
- `images/` — all image assets, referenced by plain relative path
  (`images/<name>.png`). No optimization/build pipeline; images are added
  as-is.
- `blog/index.html` — not a real blog, just a meta-refresh redirect stub to
  an external Notion page.
- Links such as `./pdfs/cv.pdf` and other `./pdfs/*.pdf` references point to
  files that are **not tracked in this repo** — they're uploaded directly to
  the host out-of-band.

## Publication entry pattern

Publications make up the bulk of `index.php` (~30 repeated blocks). Each one
is a `<tr>` with two `<td>`s and a pair of inline `<script>` functions:

```html
<tr onmouseout="foo_stop()" onmouseover="foo_start()" bgcolor="#ffffd0">
  <td style="padding:20px;width:25%;vertical-align:middle">
    <div class="one">
      <div class="two" id='foo_image'>
        <img loading="lazy" src='images/foo_start.png' width="160"></div>
      <img loading="lazy" src='images/foo_stop.png' width="160">
    </div>
    <script type="text/javascript">
    function foo_start() { document.getElementById('foo_image').style.opacity = "1"; }
    function foo_stop() { document.getElementById('foo_image').style.opacity = "0"; }
    foo_stop()
    </script>
  </td>
  <td style="padding:20px;width:75%;vertical-align:middle">
    <papertitle>Paper Title</papertitle>
    <br>
    <strong>Christoffer Löffler</strong>, Coauthor One, Coauthor Two
    <br>
    <em>Venue, Volume, Year.</em>
    <br>
    <?php  $paperid="...";  include 'citation.php'; ?>
    <a href="...">preprint</a> / <a href="...">DOI: ...</a> / <a href="...">code</a>
    <p></p>
    <p>One-paragraph abstract/summary.</p>
  </td>
</tr>
```

When adding a new entry:
- Pick a unique JS function-name prefix (`foo_start`/`foo_stop`) — these are
  all inline on one page, so a collision silently breaks the hover effect on
  both entries.
- The `id` on the inner `<div class="two" id='foo_image'>` must match the
  name used inside `foo_start`/`foo_stop`.
- Add two images to `images/` for the hover-swap pair, referenced with
  `width="160"`.
- Only include the `<?php $paperid=...; include 'citation.php'; ?>` line if
  the paper has a Google Scholar cluster id to scrape citations for —
  several entries (recent preprints, workshop papers) omit it entirely.
- `bgcolor="#ffffd0"` is used loosely to visually break up consecutive rows
  — it's not strictly alternating, so check the surrounding entries before
  deciding whether to add it.
- Self-authorship is marked by wrapping "Christoffer Löffler" in `<strong>`.

## Dev workflow

There is no package manager, build tool, linter, test suite, or CI
(`.github/workflows` doesn't exist). Deployment target/method isn't defined
in the repo — there's no `CNAME`, and since the site relies on PHP (Google
Scholar scraping), it can't be plain GitHub Pages, which doesn't execute
PHP. Treat deployment as out of scope unless the user says otherwise.

- **Preview locally**: `php -S localhost:8000` from the repo root, then open
  `http://localhost:8000/index.php`.
- **Syntax-check PHP changes**: `php -l index.php`, `php -l citation.php`,
  `php -l curl.php`, `php -l total_citations.php`.
- **Commit messages**: this repo's history uses short, imperative messages
  with an all-caps action prefix — `ADD ...`, `MOD ...` — or lowercase `fix
  ...` for bug fixes (e.g. `ADD preprint arxiv link`, `MOD colors`, `fix`).
  Follow that convention rather than Conventional Commits.
