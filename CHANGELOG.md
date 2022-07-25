## 0.2.12  

- [#51](https://github.com/SwiftAPI/swift/issues/51) Add new StringValue `\Swift\Orm\Types\FieldTypes::STRING` as field type which renders to varchar in MySQL. This field supports indexes, where TEXT does not. It also supports a length up to 255 characters. [See commit](https://github.com/SwiftAPI/swift/commit/2833be1ca49bb073df4f6b47cf21fcfd9f00d9a2)
- Pass the \Swift\Orm\Mapping\Definition\Field to `getDatabaseType` on `\Swift\Orm\Types\TypeInterface` to be able to render based on the field context (e.g.) length of the field [See commit](https://github.com/SwiftAPI/swift/commit/002f7bcc409e2fd2a38c86f429cfeea1305f56d7)
- Removed deprecated `\Swift\Orm\Dbal\Helper\QueryHelper` as it's not used anymore. [See commit](https://github.com/SwiftAPI/swift/commit/002f7bcc409e2fd2a38c86f429cfeea1305f56d7)
- Automatically add unique constraint to UUID fields [See commit](https://github.com/SwiftAPI/swift/commit/277d856ba1d4abc1da09cd201c265747c0ad3f0e)
- Fixed type for `\Swift\Orm\Orm` in `\Swift\Orm\Factory` [See commit](https://github.com/SwiftAPI/swift/commit/2d6e133747eb641e9254f82a1bd1ec68c216c45d)
- [#51](https://github.com/SwiftAPI/swift/issues/51) Add constraints again to core entities as [#51](https://github.com/SwiftAPI/swift/issues/51) is fixed [See commit](https://github.com/SwiftAPI/swift/commit/fa8e6ce86091e0b1a3f0472685283467b8f7de46)