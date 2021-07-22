<?php

declare(strict_types=1);

namespace brnc\Uuid\Domain;

use brnc\Uuid\Domain\Exception\ExceedingParameterException;
use brnc\Uuid\Domain\Exception\NoDomainUuidException;

/** @psalm-suppress PropertyNotSetInConstructor */
class DomainUuid
{
    public const TYPE_NEUTRAL   = 0;
    public const TYPE_SORTABLE  = 1;
    public const TYPE_TIMESTAMP = 2;
    public const TYPE_SHARDING  = 3;
    private const OFFSET_EPOCH   = 1599000000;

    /** @var int 16 bit for the domain identifier (e.g. mapping to tables or entities) */
    private $domain;
    /** @var int 64 bit for the model identifier (e.g. id column) */
    private $idx;
    /** @var int 4 bit for the type – see TYPE contants */
    private $type;
    /** @var int 32 bit for a »natural order« (e.g. timestamp or autoincrement) */
    private $extra;
    /** @var int additional 6 bit – MSB of the given octet (e.g [upper] microseconds or subtype etc.) */
    private $subextra;
    /** @var string */
    private $uuid;
    /** @var null|string read-back timestamp if type = 2 (fuzzy on the microseconds) */
    private $fuzzyTimestamp;

    private function __construct()
    {
    }

    public static function createFromString(string $uuid): ?self
    {
        if (!preg_match('/^(?<domain>[0-9a-f]{4})(?<extra_type>[0-9a-f])(?<extra_low>[0-9a-f]{3})-(?<extra_mid>[0-9a-f]{4})-3(?<extra_hi>[0-9a-f])(?<subextra_id_least>[0-9a-f]{2})-(?<variant_id_low>[0-9a-f])(?<id_mid>[0-9a-f]{3})-(?<id_hi>[0-9a-f]{12})$/Di', $uuid, $matches)) {
            return null;
        }
        $variantIdLowDec = hexdec($matches['variant_id_low']);
        if (2 !== $variantIdLowDec >> 2) {
            throw NoDomainUuidException::createInvalid();
        }

        $leadId = (hexdec($matches['subextra_id_least']) & 0x3) << 2 | $variantIdLowDec & 0x3;

        $instance           = new self();
        $instance->uuid     = $uuid;
        $instance->domain   = (int)hexdec($matches['domain']);
        $instance->idx      = (int)hexdec(dechex($leadId) . $matches['id_mid'] . $matches['id_hi']);
        $instance->type     = (int)hexdec($matches['extra_type']);
        $instance->extra    = (int)hexdec($matches['extra_low'] . $matches['extra_mid'] . $matches['extra_hi']);
        $instance->subextra = (int)hexdec($matches['subextra_id_least']) & 0xFC;
        if (self::TYPE_TIMESTAMP === $instance->type) {
            // ATTENTION microtime is not acurate, as it's only 6 bits deep
            $instance->fuzzyTimestamp = $instance->extra + self::OFFSET_EPOCH . '.' . substr((string)(($instance->subextra - 0.5) / 255), 2, 6);
        }

        return $instance;
    }

    public static function generateTimestamped(int $domain, int $idx): string
    {
        [$usec, $sec] = explode(' ', microtime(false));

        return self::generateFromTimestamp($domain, $idx, (int)$sec - self::OFFSET_EPOCH, (float)$usec);
    }

    public static function generateFromTimestamp(int $domain, int $idx, int $timestamp, float $microseconds = .0): string
    {
        return self::generate($domain, $idx, self::TYPE_TIMESTAMP, $timestamp, (int)(255 * $microseconds + 0.5));
    }

    public static function generate(int $domain, int $idx, int $type, int $extra, int $subextra): string
    {
        // 2 octets i.e. 16 bit to specify a domain, model or table
        $hexDomain = str_pad(dechex($domain), 4, '0', STR_PAD_LEFT);
        if (4 !== strlen($hexDomain)) {
            throw ExceedingParameterException::createExceedingDomain();
        }
        // 8 octets i.e. 64 bit to specify a model's ID
        $hexId = str_pad(dechex($idx), 16, '0', STR_PAD_LEFT);
        if (16 !== strlen($hexId)) {
            // @codeCoverageIgnoreStart
            throw ExceedingParameterException::createExceedingId();
            // @codeCoverageIgnoreEnd
        }

        $hexType = dechex($type);
        if (1 !== strlen($hexType)) {
            throw ExceedingParameterException::createExceedingType();
        }

        $hexExtra = str_pad(dechex($extra), 8, '0', STR_PAD_LEFT);
        if (8 !== strlen($hexExtra)) {
            throw ExceedingParameterException::createExceedingExtra();
        }

        if (strlen(dechex($subextra)) > 2) {
            throw ExceedingParameterException::createExceedingSubextra();
        }

        $leadId = hexdec($hexId[0]);

        return sprintf(
            '%04s%01s%03s-%04s-3%01s%02x-%01x%03s-%012s',
            // »time_low« → 16 bit domain + 4 bit extra type + 12 bit extra
            substr($hexDomain, 0, 4),
            $hexType, // Extra Type
            substr($hexExtra, 0, 3),
            // »time_mid« → 16 bit extra
            substr($hexExtra, 3, 4),
            // »time_hi_and_version« → 4 bit version (3) + 8 bit extra + 6 bit sub-extra + 2 bit ID
            $hexExtra[7],
            $subextra & 0xFC | ($leadId >> 2),
            // »clk_seq_hi_res« → 2 bit variant + 6 bit ID
            // »clk_seq_low«    → 8 bit ID
            $leadId & 0x3 | 0x8,
            substr($hexId, 1, 3),
            // »node« → 48 bit ID
            substr($hexId, 4, 12)
        );
    }

    public function getId(): int
    {
        return $this->idx;
    }
}
