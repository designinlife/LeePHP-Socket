<?php
namespace LeePHP;

class BaseException extends \Exception {}
class RuntimeException extends BaseException {}
class IOException extends BaseException {}
class ArgumentException extends BaseException {}
class DbException extends BaseException {}
class SystemException extends BaseException {}
class NotSupportException extends BaseException {}
class PermissionException extends BaseException {}
class NetworkException extends BaseException {}