# Third-party files

Everything the site loads is served from this repository. There are no CDN or
third-party requests. Each file below is an unmodified copy of the published npm
package; the checksums let you confirm that before and after any update.

## JavaScript (motion only)

Loaded by `assets/js/site.js` **after** the page has finished loading, and never for
visitors who prefer reduced motion. The site works fully without them.

| File | Package | SHA-256 | Licence |
|---|---|---|---|
| `gsap.min.js` | `gsap@3.15.0` (`dist/gsap.min.js`) | `92bb9a96476f983d212a2bc4f54c889039c1696dd4461d40a736860938570fbb` | GSAP Standard "No Charge" licence, see the header of the file and https://gsap.com/standard-license |
| `ScrollTrigger.min.js` | `gsap@3.15.0` (`dist/ScrollTrigger.min.js`) | `b0b14d67b55b0c43c756ac0b106cfcb09d0879945f6ead64451065b0672916a2` | as above |
| `lenis.min.js` | `lenis@1.3.26` (`dist/lenis.min.js`) | `53195c9797e7ce7bf9d7fa9242b08209e57f46de4c9dac126a6494fa780e3346` | MIT, [`../licenses/lenis-MIT.txt`](../licenses/lenis-MIT.txt) |

`lenis.min.js` ends with a `sourceMappingURL` comment; the map file is not shipped,
so browser DevTools may log a missing source map. Visitors never request it.

## Fonts (`../fonts/`)

From Fontsource 5.3.0 (`@fontsource/big-shoulders-display`, `@fontsource/ibm-plex-sans`,
`@fontsource/ibm-plex-mono`), latin and latin-ext subsets, `files/*.woff2`. SIL Open Font
License 1.1: [`../licenses/`](../licenses/).

## Verifying or updating

```sh
npm pack gsap@3.15.0 lenis@1.3.26     # download the published packages
tar xzf gsap-3.15.0.tgz && cmp package/dist/gsap.min.js assets/vendor/gsap.min.js
sha256sum assets/vendor/*.js           # compare with the table above
```

When updating a version, replace the file with the package's own `dist` file (never
edit it), update the version and checksum here, and re-test motion with reduced motion
both on and off.
