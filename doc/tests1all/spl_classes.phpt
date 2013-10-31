--TEST--
spl_classes
--FILE--
<?php

echo "print_r(spl_classes());\n\n";
$r=spl_classes();
print_r($r);

?>
--EXPECT--
print_r(spl_classes());

Array
(
    [AppendIterator] => AppendIterator
    [ArrayIterator] => ArrayIterator
    [ArrayObject] => ArrayObject
    [BadFunctionCallException] => BadFunctionCallException
    [BadMethodCallException] => BadMethodCallException
    [CachingIterator] => CachingIterator
    [Countable] => Countable
    [DirectoryIterator] => DirectoryIterator
    [DomainException] => DomainException
    [EmptyIterator] => EmptyIterator
    [FilterIterator] => FilterIterator
    [InfiniteIterator] => InfiniteIterator
    [InvalidArgumentException] => InvalidArgumentException
    [IteratorIterator] => IteratorIterator
    [LengthException] => LengthException
    [LimitIterator] => LimitIterator
    [LogicException] => LogicException
    [NoRewindIterator] => NoRewindIterator
    [OuterIterator] => OuterIterator
    [OutOfBoundsException] => OutOfBoundsException
    [OutOfRangeException] => OutOfRangeException
    [OverflowException] => OverflowException
    [ParentIterator] => ParentIterator
    [RangeException] => RangeException
    [RecursiveArrayIterator] => RecursiveArrayIterator
    [RecursiveCachingIterator] => RecursiveCachingIterator
    [RecursiveDirectoryIterator] => RecursiveDirectoryIterator
    [RecursiveFilterIterator] => RecursiveFilterIterator
    [RecursiveIterator] => RecursiveIterator
    [RecursiveIteratorIterator] => RecursiveIteratorIterator
    [RecursiveRegexIterator] => RecursiveRegexIterator
    [RegexIterator] => RegexIterator
    [RuntimeException] => RuntimeException
    [SeekableIterator] => SeekableIterator
    [SimpleXMLIterator] => SimpleXMLIterator
    [SplFileInfo] => SplFileInfo
    [SplFileObject] => SplFileObject
    [SplObjectStorage] => SplObjectStorage
    [SplObserver] => SplObserver
    [SplSubject] => SplSubject
    [SplTempFileObject] => SplTempFileObject
    [UnderflowException] => UnderflowException
    [UnexpectedValueException] => UnexpectedValueException
)
