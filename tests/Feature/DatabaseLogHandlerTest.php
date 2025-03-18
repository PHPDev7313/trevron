<?php

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use JDS\Auditor\Exception\InvalidArgumentException;
use JDS\Auditor\Handlers\DatabaseLogHandler;
use JDS\Auditor\Provider\LogLevelProvider;
use JDS\Auditor\Validators\DatabaseLogJsonValidator;
use JDS\Dbal\GenerateNewId;

function getMockedDatabaseLogHandler()
{
    $mockConnection = Mockery::mock(Connection::class);
    $mockQueryBuilder = Mockery::mock(QueryBuilder::class);

    $mockLogLevelProvider = Mockery::mock(LogLevelProvider::class);
    $mockJsonValidator = Mockery::mock(DatabaseLogJsonValidator::class);
    $mockIdGenerator = Mockery::mock(GenerateNewId::class);

    return [
        'handler' => new DatabaseLogHandler($mockConnection,
            'log_table',
        $mockIdGenerator,
        $mockLogLevelProvider,
        $mockJsonValidator,
        ),
        'connection' => $mockConnection,
        'queryBuilder' => $mockQueryBuilder,
        'logLevelProvider' => $mockLogLevelProvider,
        'jsonValidator' => $mockJsonValidator,
        'idGenerator' => $mockIdGenerator,
    ];
}

// 1. Test:
it('throws an exception if the log entry lacks level or message', function () {
    $setup = getMockedDatabaseLogHandler();
    $handler = $setup['handler'];
    $logEntry = ['context' => []]; // missing 'level' and 'message'


    // Expect for an InvalidArgumentException
    expect(fn () => $handler->handle($logEntry))
        ->toThrow(InvalidArgumentException::class, 'Log entry must have a "level" and "message".');
//    $this->expectException(InvalidArgumentException::class);
//    $this->expectExceptionMessage('Log entry must have a "level" and "message".');
//    // act
//    $handler->handle($logEntry);
});

// 2. Test: test that 'handle throws an exception for an invalid level
it('throws an exception if the log level is invalid', function () {
   $setup = getMockedDatabaseLogHandler();
   $handler = $setup['handler'];
   $logEntry = [
       'level' => 'INVALID_LEVEL',
       'message' => 'A test message',
   ];

   // mocking valid levels
    $setup['logLevelProvider']
        ->shouldReceive('getValidLevels')
        ->once()
        ->andReturn(['DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY']);

    // exceptions for an InvalidArgumentException
    expect(fn () => $handler->handle($logEntry))
        ->toThrow(InvalidArgumentException::class, 'Invalid Log Level.');

//    $this->expectException(InvalidArgumentException::class);
//    $this->expectExceptionMessage('Invalid Log Level.');
//
//    // act
//    $handler->handle($logEntry);
});

// 3. Test: Test valid `handle` log entry works as expected
it('inserts a log into the database if all inputs are valid', function () {
    $setup = getMockedDatabaseLogHandler();
    $handler = $setup['handler'];

    $logEntry = [
        'level' => 'INFO',
        'message' => 'A test message',
        'context' => ['key' => 'value'],
    ];

    // Mocking valid levels
    $setup['logLevelProvider']
        ->shouldReceive('getValidLogLevels')
        ->once()
        ->andReturn(['DEBUG', 'INFO', 'ERROR']);

    // Mocking validator
    $setup['jsonValidator']
        ->shouldReceive('validateAndEncode')
        ->once()
        ->with($logEntry['context'])
        ->andReturn(json_encode($logEntry['context']));

    // Mocking ID generator
    $setup['idGenerator']
        ->shouldReceive('getNewId')
        ->once()
        ->andReturn('mocked-id');

    // Mocking database connection and SQL execution
    $mockStatement = Mockery::mock();
    $mockStatement->shouldReceive('bindValue')
        ->atLeast()
        ->once();
    $mockStatement->shouldReceive('executeStatement')
        ->once();

    $setup['connection']
        ->shouldReceive('prepare')
        ->once()
        ->andReturn($mockStatement);

    // Act
    expect(fn () => $handler->handle($logEntry))
        ->not->toThrow(Throwable::class);

//    $handler->handle($logEntry);
//
//    // Assert - No exceptions thrown, the SQL statement executes
//    expect(true)->toBeTrue();
});

// 4. Test: Test the `readLog` function retrieves filtered logs
it('retrieves logs based on filters using readLog', function () {
    $setup = getMockedDatabaseLogHandler();
    $handler = $setup['handler'];

    $filters = [
        'level' => 'INFO',
        'startDate' => '2023-01-01',
        'endDate' => '2023-12-31',
        'limit' => 10,
    ];

    $setup['queryBuilder']
        ->shouldReceive('select')
        ->once()
        ->with('*')
        ->andReturnSelf();
    $setup['queryBuilder']
        ->shouldReceive('from')
        ->once()
        ->with('log_table')
        ->andReturnSelf();
    $setup['queryBuilder']
        ->shouldReceive('setMaxResults')
        ->once()
        ->with($filters['limit'])
        ->andReturnSelf();
    $setup['queryBuilder']
        ->shouldReceive('orderBy')
        ->once()
        ->with('created', 'DESC')
        ->andReturnSelf();
    $setup['queryBuilder']
        ->shouldReceive('andWhere')
        ->once()
        ->with('level = :level')
        ->andReturnSelf();
    $setup['queryBuilder']
        ->shouldReceive('setParameter')
        ->once()
        ->with('level', $filters['level'])
        ->andReturnSelf();
    $setup['queryBuilder']
        ->shouldReceive('andWhere')
        ->once()
        ->with('created >= :startDate')
        ->andReturnSelf();
    $setup['queryBuilder']
        ->shouldReceive('setParameter')
        ->once()
        ->with('startDate', $filters['startDate'])
        ->andReturnSelf();
    $setup['queryBuilder']
        ->shouldReceive('andWhere')
        ->once()
        ->with('created <= :endDate')
        ->andReturnSelf();
    $setup['queryBuilder']
        ->shouldReceive('setParameter')
        ->once()
        ->with('endDate', $filters['endDate'])
        ->andReturnSelf();
    $setup['queryBuilder']
        ->shouldReceive('fetchAllAssociative')
        ->once()
        ->andReturn([
            ['level' => 'INFO', 'message' => 'Test log', 'created' => '2023-06-01'],
        ]);

    $setup['connection']
        ->shouldReceive('createQueryBuilder')
        ->once()
        ->andReturn($setup['queryBuilder']);

    // act
    $result = $handler->readLog(
        $filters['level'],
        $filters['startDate'],
        $filters['endDate'],
        $filters['limit']
    );

    // assert
    expect($result)->toBeArray()->toHaveCount(1);
    expect($result[0]['level'])->toBe('INFO');
    expect($result[0]['message'])->toBe('Test log');


//    // Mocking QueryBuilder
//    $queryBuilder = Mockery::mock(QueryBuilder::class);
//
//    // Ensure filtering is properly applied
//    $queryBuilder
//        ->shouldReceive('select')
//        ->once()
//        ->with('*')
//        ->andReturnSelf();
//    $queryBuilder->shouldReceive('from')->once()->with('log_table')->andReturnSelf();
//    $queryBuilder->shouldReceive('setMaxResults')->once()->with($filters['limit'])->andReturnSelf();
//    $queryBuilder->shouldReceive('orderBy')->once()->with('created', 'DESC')->andReturnSelf();
//
//    // Adding filters
//    $queryBuilder->shouldReceive('andWhere')->once()->with('level = :level')->andReturnSelf();
//    $queryBuilder->shouldReceive('setParameter')->once()->with('level', $filters['level'])->andReturnSelf();
//    $queryBuilder->shouldReceive('andWhere')->once()->with('created >= :startDate')->andReturnSelf();
//    $queryBuilder->shouldReceive('setParameter')->once()->with('startDate', $filters['startDate'])->andReturnSelf();
//    $queryBuilder->shouldReceive('andWhere')->once()->with('created <= :endDate')->andReturnSelf();
//    $queryBuilder->shouldReceive('setParameter')->once()->with('endDate', $filters['endDate'])->andReturnSelf();
//
//    // Mock the return value of fetchAllAssociative
//    $queryBuilder
//        ->shouldReceive('fetchAllAssociative')
//        ->once()
//        ->andReturn([
//            ['level' => 'INFO', 'message' => 'Test log', 'created' => '2023-06-01'],
//        ]);
//
//    $setup['connection']
//        ->shouldReceive('createQueryBuilder')
//        ->once()
//        ->andReturn($queryBuilder);
//
//    // Act
//    $result = $handler->readLog(
//        $filters['level'],
//        $filters['startDate'],
//        $filters['endDate'],
//        $filters['limit']
//    );
//
//    // Assert
//    expect($result)->toBeArray()->toHaveCount(1);
//    expect($result[0]['level'])->toBe('INFO');
//    expect($result[0]['message'])->toBe('Test log');
});

// 5. Test: `handle` throws an exception if the database operation fails
it('throws an exception if the database operation fails', function () {
    $setup = getMockedDatabaseLogHandler();
    $handler = $setup['handler'];
    $logEntry = [
        'level' => 'INFO',
        'message' => 'Failing log',
        'context' => [],
    ];

    $setup['logLevelProvider']
        ->shouldReceive('getValidLevels')
        ->once()
        ->andReturn(['DEBUG', 'INFO', 'WARNING']);
    $setup['idGenerator']
        ->shouldReceive('getNewId')
        ->once()
        ->andReturn('mocked-id');
    $setup['connection']
        ->shouldReceive('prepare')
        ->once()
        ->andThrow(new Exception('Database error'));

    expect(fn () => $handler->handle($logEntry))
        ->toThrow(Exception::class, 'Database error');
});


