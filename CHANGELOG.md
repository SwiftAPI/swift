## 0.2.14

- New: Rate Limiting [See commit](https://github.com/SwiftAPI/swift/commit/37ecc1ccc25ae6e5f62ca1ee8bf4b4e2f045c503)
- New: Login Throttling [See commit](https://github.com/SwiftAPI/swift/commit/ead6f738952278276482322af2132716a04c557a)
- Add support for custom GraphQl validation rules + additional security settings [See commit](https://github.com/SwiftAPI/swift/commit/cb7df1d2b3f0e8804962ae37840445154f0993f7)
- Fix: REQUEST_PARSING priority was missing in KernelMiddleware [See commit](https://github.com/SwiftAPI/swift/commit/627b33434cc4ba32bdfbdd0701d9dd44239c52d8)
- Bump dependency versions [See commit](https://github.com/SwiftAPI/swift/commit/c9913666990b1882a204f37059e76abf963eca23)

## 0.2.13

- Bump cycle/orm minimum version to v2.2.0 [See commit](https://github.com/SwiftAPI/swift/commit/60bd721d4e4d3461323e7df033138e48441a2929)
- Fix `\Swift\Logging\AbstractLogger` accessing dispatcher before it was initialized [See commit](https://github.com/SwiftAPI/swift/commit/1f6e54987b04f85e0a308bc5449676ed15fc54b3)
- Support tagged iterator injection through constructor [See commit](https://github.com/SwiftAPI/swift/commit/995ef28ccc9fe56c5d4e876fd3a8b4a53a98750b)
- Simplified Application Bootstrap [See commit](https://github.com/SwiftAPI/swift/commit/1775329b8888ac619eb991703da6a3f3ea4a1d56)
- Improved PSR Compliance [See commit](https://github.com/SwiftAPI/swift/commit/9bb62add9c373e580d005b8781764f4b84cee2b8)
- Implement Middleware pattern in Application [See commit](https://github.com/SwiftAPI/swift/commit/5269c42819ecd006cb2b66d0346cdcddc015e2e9)
- Add CORS, default timezone setting, deprecation setting, request logging, REST routing, GraphQl, and authentication to Middleware [See commit](https://github.com/SwiftAPI/swift/commit/b04dd60337aa925a7e8d018ee70a204580ddf41e)
- Improved support for asynchronous flows [See commit](https://github.com/SwiftAPI/swift/commit/b04dd60337aa925a7e8d018ee70a204580ddf41e)

## 0.2.12  

- [#51](https://github.com/SwiftAPI/swift/issues/51) Add new StringValue `\Swift\Orm\Types\FieldTypes::STRING` as field type which renders to varchar in MySQL. This field supports indexes, where TEXT does not. It also supports a length up to 255 characters. [See commit](https://github.com/SwiftAPI/swift/commit/2833be1ca49bb073df4f6b47cf21fcfd9f00d9a2)
- Pass the \Swift\Orm\Mapping\Definition\Field to `getDatabaseType` on `\Swift\Orm\Types\TypeInterface` to be able to render based on the field context (e.g.) length of the field [See commit](https://github.com/SwiftAPI/swift/commit/002f7bcc409e2fd2a38c86f429cfeea1305f56d7)
- Removed deprecated `\Swift\Orm\Dbal\Helper\QueryHelper` as it's not used anymore. [See commit](https://github.com/SwiftAPI/swift/commit/002f7bcc409e2fd2a38c86f429cfeea1305f56d7)
- Automatically add unique constraint to UUID fields [See commit](https://github.com/SwiftAPI/swift/commit/277d856ba1d4abc1da09cd201c265747c0ad3f0e)
- Fixed type for `\Swift\Orm\Orm` in `\Swift\Orm\Factory` [See commit](https://github.com/SwiftAPI/swift/commit/2d6e133747eb641e9254f82a1bd1ec68c216c45d)
- [#51](https://github.com/SwiftAPI/swift/issues/51) Add constraints again to core entities as [#51](https://github.com/SwiftAPI/swift/issues/51) is fixed [See commit](https://github.com/SwiftAPI/swift/commit/fa8e6ce86091e0b1a3f0472685283467b8f7de46)