<?php
/**
 * Rating System Helper Functions
 * 
 * This file contains utility functions for managing the rating system,
 * including aggregate calculations and data consistency operations.
 */

require_once 'db.php';

/**
 * Updates service aggregates (average rating and count) for a given service
 * 
 * @param PDO $pdo Database connection
 * @param int $service_id Service ID to update aggregates for
 * @return bool True on success, false on failure
 */
function updateServiceAggregates($pdo, $service_id) {
    try {
        // Calculate new aggregates from ratings table
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(AVG(rating), 0) as avg_rating,
                COUNT(*) as total_count
            FROM ratings 
            WHERE service_id = ?
        ");
        $stmt->execute([$service_id]);
        $aggregates = $stmt->fetch();
        
        // Update services table with calculated values
        $stmt = $pdo->prepare("
            UPDATE services 
            SET average_rating = ?, rating_count = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            round($aggregates['avg_rating'], 2), 
            $aggregates['total_count'], 
            $service_id
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Error updating service aggregates for service $service_id: " . $e->getMessage());
        return false;
    }
}

/**
 * Gets all ratings for a specific service
 * 
 * @param PDO $pdo Database connection
 * @param int $service_id Service ID to get ratings for
 * @return array Array of rating records with user information
 */
function getServiceRatings($pdo, $service_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT r.rating, r.created_at, u.name as user_name 
            FROM ratings r 
            LEFT JOIN users u ON r.user_id = u.id 
            WHERE r.service_id = ? 
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$service_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting service ratings for service $service_id: " . $e->getMessage());
        return [];
    }
}

/**
 * Gets service aggregates (average rating and count)
 * 
 * @param PDO $pdo Database connection
 * @param int $service_id Service ID to get aggregates for
 * @return array Associative array with 'average_rating' and 'rating_count'
 */
function getServiceAggregates($pdo, $service_id) {
    try {
        // Try to get from services table first (if columns exist)
        $stmt = $pdo->prepare("
            SELECT average_rating, rating_count 
            FROM services 
            WHERE id = ?
        ");
        $stmt->execute([$service_id]);
        $result = $stmt->fetch();
        
        if ($result && isset($result['average_rating'])) {
            return [
                'average_rating' => (float)$result['average_rating'],
                'rating_count' => (int)$result['rating_count']
            ];
        }
    } catch (PDOException $e) {
        // Columns might not exist, calculate from ratings table
    }
    
    // Fallback: calculate from ratings table
    try {
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(AVG(rating), 0) as average_rating,
                COUNT(*) as rating_count
            FROM ratings 
            WHERE service_id = ?
        ");
        $stmt->execute([$service_id]);
        $result = $stmt->fetch();
        
        return [
            'average_rating' => round((float)$result['average_rating'], 2),
            'rating_count' => (int)$result['rating_count']
        ];
    } catch (PDOException $e) {
        error_log("Error getting service aggregates for service $service_id: " . $e->getMessage());
        return ['average_rating' => 0, 'rating_count' => 0];
    }
}

/**
 * Verifies aggregate consistency for a service
 * 
 * @param PDO $pdo Database connection
 * @param int $service_id Service ID to verify
 * @return bool True if consistent, false if inconsistent
 */
function verifyAggregateConsistency($pdo, $service_id) {
    try {
        // Get stored aggregates
        $stored = getServiceAggregates($pdo, $service_id);
        
        // Calculate actual aggregates
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(AVG(rating), 0) as avg_rating,
                COUNT(*) as total_count
            FROM ratings 
            WHERE service_id = ?
        ");
        $stmt->execute([$service_id]);
        $actual = $stmt->fetch();
        
        $actual_avg = round((float)$actual['avg_rating'], 2);
        $actual_count = (int)$actual['total_count'];
        
        // Check if they match (with small tolerance for floating point)
        $avg_match = abs($stored['average_rating'] - $actual_avg) < 0.01;
        $count_match = $stored['rating_count'] === $actual_count;
        
        return $avg_match && $count_match;
    } catch (PDOException $e) {
        error_log("Error verifying aggregate consistency for service $service_id: " . $e->getMessage());
        return false;
    }
}

/**
 * Recalculates aggregates for all services
 * 
 * @param PDO $pdo Database connection
 * @return int Number of services updated
 */
function recalculateAllServiceAggregates($pdo) {
    try {
        // Get all service IDs
        $stmt = $pdo->prepare("SELECT DISTINCT id FROM services");
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $updated_count = 0;
        foreach ($services as $service_id) {
            if (updateServiceAggregates($pdo, $service_id)) {
                $updated_count++;
            }
        }
        
        return $updated_count;
    } catch (PDOException $e) {
        error_log("Error recalculating all service aggregates: " . $e->getMessage());
        return 0;
    }
}

/**
 * Validates rating value
 * 
 * @param mixed $rating Rating value to validate
 * @return bool True if valid (1-5), false otherwise
 */
function isValidRating($rating) {
    $rating = (int)$rating;
    return $rating >= 1 && $rating <= 5;
}

/**
 * Checks if user has already rated a service
 * 
 * @param PDO $pdo Database connection
 * @param int $service_id Service ID
 * @param int $user_id User ID
 * @return int|null Current rating if exists, null otherwise
 */
function getUserRating($pdo, $service_id, $user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT rating 
            FROM ratings 
            WHERE service_id = ? AND user_id = ?
        ");
        $stmt->execute([$service_id, $user_id]);
        $result = $stmt->fetch();
        
        return $result ? (int)$result['rating'] : null;
    } catch (PDOException $e) {
        error_log("Error getting user rating: " . $e->getMessage());
        return null;
    }
}