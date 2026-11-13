-- ========================================================
-- CINEMA BOOKING MIGRATION 002
-- Safe Seed of Master Catalogue (Cities, Theatres, Screens, Seats, Movies, Shows)
-- Uses INSERT IGNORE to prevent duplicate rows and preserve existing records.
-- NOTE: Does NOT insert demo administrators, dummy users, or test bookings!
-- ========================================================

-- 1. Cities
INSERT IGNORE INTO `cities` (`id`, `name`, `state`, `is_active`) VALUES
(1, 'Mumbai', 'Maharashtra', 1),
(2, 'Delhi NCR', 'Delhi', 1),
(3, 'Bengaluru', 'Karnataka', 1),
(4, 'Hyderabad', 'Telangana', 1);

-- 2. Theatres
INSERT IGNORE INTO `theatres` (`id`, `city_id`, `name`, `address`, `contact_phone`, `facilities`, `is_active`) VALUES
(1, 1, 'CineLux IMAX & 4DX, Lower Parel', 'Phoenix Palladium, High Street Phoenix, Senapati Bapat Marg, Lower Parel, Mumbai', '+91 22 4001 5000', 'IMAX Laser, Dolby Atmos, Luxury Recliners, Gourmet Food, Valet Parking', 1),
(2, 1, 'PVR Grand StarPlex, Bandra', 'Linking Road, Bandra West, Mumbai', '+91 22 2640 1200', '4K Projection, Dolby Atmos 7.1, Recliners, Cafe', 1),
(3, 2, 'CineLux Royale, Connaught Place', 'Regal Building, Connaught Place, New Delhi', '+91 11 2334 8899', 'IMAX Laser 3D, Dolby Atmos, Gold Class Lounge, Valet Parking', 1),
(4, 2, 'StarPlex Multiplex, Gurgaon', 'Cyber Hub, DLF Phase 2, Gurugram', '+91 124 456 7890', '4DX Motion, Dolby Atmos, Snack Bar, Gaming Lounge', 1),
(5, 3, 'CineLux IMAX, Koramangala', 'Forum Mall, Hosur Road, Koramangala, Bengaluru', '+91 80 2206 7800', 'IMAX Laser, Dolby Atmos, Recliners, Free WiFi, Cafe', 1),
(6, 4, 'Prasads CineLux, Banjara Hills', 'Road No. 2, Banjara Hills, Hyderabad', '+91 40 2345 6789', 'IMAX Giant Screen, 4K Laser, Dolby Atmos, Food Court', 1);

-- 3. Screens
INSERT IGNORE INTO `screens` (`id`, `theatre_id`, `screen_name`, `screen_type`, `total_seats`) VALUES
(1, 1, 'Screen 1 - IMAX Laser', 'IMAX Laser 3D', 60),
(2, 1, 'Screen 2 - Dolby Atmos', 'Dolby Atmos 2D', 60),
(3, 2, 'Audi 1 - Royale Gold', 'Gold Class Recliner', 60),
(4, 3, 'Screen 1 - IMAX Laser', 'IMAX Laser 3D', 60),
(5, 4, 'Screen 2 - 4DX Dynamic', '4DX Motion 3D', 60),
(6, 5, 'Screen 1 - IMAX Laser', 'IMAX Laser 3D', 60);

-- 4. Seats for Screens 1, 2, 3
-- Screen 1
INSERT IGNORE INTO `seats` (`screen_id`, `seat_row`, `seat_number`, `seat_code`, `seat_type`, `is_active`) VALUES
(1, 'A', 1, 'A1', 'recliner', 1), (1, 'A', 2, 'A2', 'recliner', 1), (1, 'A', 3, 'A3', 'recliner', 1), (1, 'A', 4, 'A4', 'recliner', 1), (1, 'A', 5, 'A5', 'recliner', 1), (1, 'A', 6, 'A6', 'recliner', 1), (1, 'A', 7, 'A7', 'recliner', 1), (1, 'A', 8, 'A8', 'recliner', 1), (1, 'A', 9, 'A9', 'recliner', 1), (1, 'A', 10, 'A10', 'recliner', 1),
(1, 'B', 1, 'B1', 'premium', 1),  (1, 'B', 2, 'B2', 'premium', 1),  (1, 'B', 3, 'B3', 'premium', 1),  (1, 'B', 4, 'B4', 'premium', 1),  (1, 'B', 5, 'B5', 'premium', 1),  (1, 'B', 6, 'B6', 'premium', 1),  (1, 'B', 7, 'B7', 'premium', 1),  (1, 'B', 8, 'B8', 'premium', 1),  (1, 'B', 9, 'B9', 'premium', 1),  (1, 'B', 10, 'B10', 'premium', 1),
(1, 'C', 1, 'C1', 'premium', 1),  (1, 'C', 2, 'C2', 'premium', 1),  (1, 'C', 3, 'C3', 'premium', 1),  (1, 'C', 4, 'C4', 'premium', 1),  (1, 'C', 5, 'C5', 'premium', 1),  (1, 'C', 6, 'C6', 'premium', 1),  (1, 'C', 7, 'C7', 'premium', 1),  (1, 'C', 8, 'C8', 'premium', 1),  (1, 'C', 9, 'C9', 'premium', 1),  (1, 'C', 10, 'C10', 'premium', 1),
(1, 'D', 1, 'D1', 'premium', 1),  (1, 'D', 2, 'D2', 'premium', 1),  (1, 'D', 3, 'D3', 'premium', 1),  (1, 'D', 4, 'D4', 'premium', 1),  (1, 'D', 5, 'D5', 'premium', 1),  (1, 'D', 6, 'D6', 'premium', 1),  (1, 'D', 7, 'D7', 'premium', 1),  (1, 'D', 8, 'D8', 'premium', 1),  (1, 'D', 9, 'D9', 'premium', 1),  (1, 'D', 10, 'D10', 'premium', 1),
(1, 'E', 1, 'E1', 'regular', 1),  (1, 'E', 2, 'E2', 'regular', 1),  (1, 'E', 3, 'E3', 'regular', 1),  (1, 'E', 4, 'E4', 'regular', 1),  (1, 'E', 5, 'E5', 'regular', 1),  (1, 'E', 6, 'E6', 'regular', 1),  (1, 'E', 7, 'E7', 'regular', 1),  (1, 'E', 8, 'E8', 'regular', 1),  (1, 'E', 9, 'E9', 'regular', 1),  (1, 'E', 10, 'E10', 'regular', 1),
(1, 'F', 1, 'F1', 'regular', 1),  (1, 'F', 2, 'F2', 'regular', 1),  (1, 'F', 3, 'F3', 'regular', 1),  (1, 'F', 4, 'F4', 'regular', 1),  (1, 'F', 5, 'F5', 'regular', 1),  (1, 'F', 6, 'F6', 'regular', 1),  (1, 'F', 7, 'F7', 'regular', 1),  (1, 'F', 8, 'F8', 'regular', 1),  (1, 'F', 9, 'F9', 'regular', 1),  (1, 'F', 10, 'F10', 'regular', 1);

-- Screen 2
INSERT IGNORE INTO `seats` (`screen_id`, `seat_row`, `seat_number`, `seat_code`, `seat_type`, `is_active`) VALUES
(2, 'A', 1, 'A1', 'recliner', 1), (2, 'A', 2, 'A2', 'recliner', 1), (2, 'A', 3, 'A3', 'recliner', 1), (2, 'A', 4, 'A4', 'recliner', 1), (2, 'A', 5, 'A5', 'recliner', 1), (2, 'A', 6, 'A6', 'recliner', 1), (2, 'A', 7, 'A7', 'recliner', 1), (2, 'A', 8, 'A8', 'recliner', 1), (2, 'A', 9, 'A9', 'recliner', 1), (2, 'A', 10, 'A10', 'recliner', 1),
(2, 'B', 1, 'B1', 'premium', 1),  (2, 'B', 2, 'B2', 'premium', 1),  (2, 'B', 3, 'B3', 'premium', 1),  (2, 'B', 4, 'B4', 'premium', 1),  (2, 'B', 5, 'B5', 'premium', 1),  (2, 'B', 6, 'B6', 'premium', 1),  (2, 'B', 7, 'B7', 'premium', 1),  (2, 'B', 8, 'B8', 'premium', 1),  (2, 'B', 9, 'B9', 'premium', 1),  (2, 'B', 10, 'B10', 'premium', 1),
(2, 'C', 1, 'C1', 'premium', 1),  (2, 'C', 2, 'C2', 'premium', 1),  (2, 'C', 3, 'C3', 'premium', 1),  (2, 'C', 4, 'C4', 'premium', 1),  (2, 'C', 5, 'C5', 'premium', 1),  (2, 'C', 6, 'C6', 'premium', 1),  (2, 'C', 7, 'C7', 'premium', 1),  (2, 'C', 8, 'C8', 'premium', 1),  (2, 'C', 9, 'C9', 'premium', 1),  (2, 'C', 10, 'C10', 'premium', 1),
(2, 'D', 1, 'D1', 'premium', 1),  (2, 'D', 2, 'D2', 'premium', 1),  (2, 'D', 3, 'D3', 'premium', 1),  (2, 'D', 4, 'D4', 'premium', 1),  (2, 'D', 5, 'D5', 'premium', 1),  (2, 'D', 6, 'D6', 'premium', 1),  (2, 'D', 7, 'D7', 'premium', 1),  (2, 'D', 8, 'D8', 'premium', 1),  (2, 'D', 9, 'D9', 'premium', 1),  (2, 'D', 10, 'D10', 'premium', 1),
(2, 'E', 1, 'E1', 'regular', 1),  (2, 'E', 2, 'E2', 'regular', 1),  (2, 'E', 3, 'E3', 'regular', 1),  (2, 'E', 4, 'E4', 'regular', 1),  (2, 'E', 5, 'E5', 'regular', 1),  (2, 'E', 6, 'E6', 'regular', 1),  (2, 'E', 7, 'E7', 'regular', 1),  (2, 'E', 8, 'E8', 'regular', 1),  (2, 'E', 9, 'E9', 'regular', 1),  (2, 'E', 10, 'E10', 'regular', 1),
(2, 'F', 1, 'F1', 'regular', 1),  (2, 'F', 2, 'F2', 'regular', 1),  (2, 'F', 3, 'F3', 'regular', 1),  (2, 'F', 4, 'F4', 'regular', 1),  (2, 'F', 5, 'F5', 'regular', 1),  (2, 'F', 6, 'F6', 'regular', 1),  (2, 'F', 7, 'F7', 'regular', 1),  (2, 'F', 8, 'F8', 'regular', 1),  (2, 'F', 9, 'F9', 'regular', 1),  (2, 'F', 10, 'F10', 'regular', 1);

-- Screen 3
INSERT IGNORE INTO `seats` (`screen_id`, `seat_row`, `seat_number`, `seat_code`, `seat_type`, `is_active`) VALUES
(3, 'A', 1, 'A1', 'recliner', 1), (3, 'A', 2, 'A2', 'recliner', 1), (3, 'A', 3, 'A3', 'recliner', 1), (3, 'A', 4, 'A4', 'recliner', 1), (3, 'A', 5, 'A5', 'recliner', 1), (3, 'A', 6, 'A6', 'recliner', 1), (3, 'A', 7, 'A7', 'recliner', 1), (3, 'A', 8, 'A8', 'recliner', 1), (3, 'A', 9, 'A9', 'recliner', 1), (3, 'A', 10, 'A10', 'recliner', 1),
(3, 'B', 1, 'B1', 'premium', 1),  (3, 'B', 2, 'B2', 'premium', 1),  (3, 'B', 3, 'B3', 'premium', 1),  (3, 'B', 4, 'B4', 'premium', 1),  (3, 'B', 5, 'B5', 'premium', 1),  (3, 'B', 6, 'B6', 'premium', 1),  (3, 'B', 7, 'B7', 'premium', 1),  (3, 'B', 8, 'B8', 'premium', 1),  (3, 'B', 9, 'B9', 'premium', 1),  (3, 'B', 10, 'B10', 'premium', 1),
(3, 'C', 1, 'C1', 'premium', 1),  (3, 'C', 2, 'C2', 'premium', 1),  (3, 'C', 3, 'C3', 'premium', 1),  (3, 'C', 4, 'C4', 'premium', 1),  (3, 'C', 5, 'C5', 'premium', 1),  (3, 'C', 6, 'C6', 'premium', 1),  (3, 'C', 7, 'C7', 'premium', 1),  (3, 'C', 8, 'C8', 'premium', 1),  (3, 'C', 9, 'C9', 'premium', 1),  (3, 'C', 10, 'C10', 'premium', 1),
(3, 'D', 1, 'D1', 'premium', 1),  (3, 'D', 2, 'D2', 'premium', 1),  (3, 'D', 3, 'D3', 'premium', 1),  (3, 'D', 4, 'D4', 'premium', 1),  (3, 'D', 5, 'D5', 'premium', 1),  (3, 'D', 6, 'D6', 'premium', 1),  (3, 'D', 7, 'D7', 'premium', 1),  (3, 'D', 8, 'D8', 'premium', 1),  (3, 'D', 9, 'D9', 'premium', 1),  (3, 'D', 10, 'D10', 'premium', 1),
(3, 'E', 1, 'E1', 'regular', 1),  (3, 'E', 2, 'E2', 'regular', 1),  (3, 'E', 3, 'E3', 'regular', 1),  (3, 'E', 4, 'E4', 'regular', 1),  (3, 'E', 5, 'E5', 'regular', 1),  (3, 'E', 6, 'E6', 'regular', 1),  (3, 'E', 7, 'E7', 'regular', 1),  (3, 'E', 8, 'E8', 'regular', 1),  (3, 'E', 9, 'E9', 'regular', 1),  (3, 'E', 10, 'E10', 'regular', 1),
(3, 'F', 1, 'F1', 'regular', 1),  (3, 'F', 2, 'F2', 'regular', 1),  (3, 'F', 3, 'F3', 'regular', 1),  (3, 'F', 4, 'F4', 'regular', 1),  (3, 'F', 5, 'F5', 'regular', 1),  (3, 'F', 6, 'F6', 'regular', 1),  (3, 'F', 7, 'F7', 'regular', 1),  (3, 'F', 8, 'F8', 'regular', 1),  (3, 'F', 9, 'F9', 'regular', 1),  (3, 'F', 10, 'F10', 'regular', 1);

-- Screen 4, 5, 6
INSERT IGNORE INTO `seats` (`screen_id`, `seat_row`, `seat_number`, `seat_code`, `seat_type`, `is_active`)
SELECT 4, `seat_row`, `seat_number`, `seat_code`, `seat_type`, `is_active` FROM `seats` WHERE `screen_id` = 1;
INSERT IGNORE INTO `seats` (`screen_id`, `seat_row`, `seat_number`, `seat_code`, `seat_type`, `is_active`)
SELECT 5, `seat_row`, `seat_number`, `seat_code`, `seat_type`, `is_active` FROM `seats` WHERE `screen_id` = 1;
INSERT IGNORE INTO `seats` (`screen_id`, `seat_row`, `seat_number`, `seat_code`, `seat_type`, `is_active`)
SELECT 6, `seat_row`, `seat_number`, `seat_code`, `seat_type`, `is_active` FROM `seats` WHERE `screen_id` = 1;

-- 5. Master Movies Catalogue
INSERT IGNORE INTO `movies` (`id`, `title`, `slug`, `tmdb_id`, `description`, `duration_mins`, `release_date`, `genre`, `language`, `rating`, `certificate`, `poster_url`, `backdrop_url`, `trailer_url`, `status`, `is_featured`) VALUES
(1, 'The Batman', 'the-batman-2022', 414906, 'When a sadistic serial killer begins murdering key political figures in Gotham, the Batman is forced to investigate the city\'s hidden corruption and question his family\'s involvement.', 176, '2022-03-04', 'Action, Crime, Drama', 'English', 7.9, 'UA', 'assets/images/The Batman(2022).jpg', 'assets/images/The Batman(2022).jpg', 'https://www.youtube.com/watch?v=mqqft2x_Aa4', 'now_showing', 1),
(2, 'Avatar: The Way of Water', 'avatar-the-way-of-water', 76600, 'Jake Sully lives with his newfound family formed on the extrasolar moon Pandora. Once a familiar threat returns to finish what was previously started, Jake must work with Neytiri and the army of the Na\'vi race to protect their home.', 192, '2022-12-16', 'Sci-Fi, Action, Adventure', 'English', 7.6, 'UA', 'assets/images/Avatar The Way Of Water.jpg', 'assets/images/download (5).jpg', 'https://www.youtube.com/watch?v=d9MyW72ELq0', 'now_showing', 1),
(3, 'RRR (Rise Roar Revolt)', 'rrr-rise-roar-revolt', 579974, 'A fearless warrior on a perilous mission comes face to face with a steely cop serving British forces in this epic saga set in pre-independent India.', 187, '2022-03-25', 'Action, Drama', 'Hindi', 8.0, 'UA', 'assets/images/Movies Poster.jpg', 'assets/images/Movies Poster.jpg', 'https://www.youtube.com/watch?v=GY4BgdUSpbE', 'now_showing', 1),
(4, 'Titanic', 'titanic-1997', 597, 'A seventeen-year-old aristocrat falls in love with a kind but poor artist aboard the luxurious, ill-fated R.M.S. Titanic in James Cameron\'s timeless cinematic masterpiece.', 195, '1997-12-19', 'Drama, Romance', 'English', 7.9, 'UA', 'assets/images/Titanic Collection _ PG-13 ~ Drama, Romance = Titanic- 1997.jpg', 'assets/images/Titanic Collection _ PG-13 ~ Drama, Romance = Titanic- 1997.jpg', 'https://www.youtube.com/watch?v=I7c1etV7D7g', 'now_showing', 1),
(5, 'Vikram', 'vikram-2022', 864692, 'A special investigator is assigned a case of serial killings, leading him to a war between an elusive masked gang and a ruthless drug cartel lord.', 175, '2022-06-03', 'Action, Crime, Thriller', 'Tamil', 8.3, 'UA', 'assets/images/Vikram (2022) - Lokesh Kanagaraj.jpg', 'assets/images/Vikram (2022) - Lokesh Kanagaraj.jpg', 'https://www.youtube.com/watch?v=OKBMCL-frPU', 'now_showing', 0),
(6, 'Pushpa 3: The Rampage', 'pushpa-3-the-rampage', 872585, 'Pushpa Raj expands his syndicate beyond borders, facing international adversaries and law enforcement in a climactic war for ultimate dominance.', 165, '2026-08-15', 'Action, Crime, Drama', 'Telugu', 8.4, 'A', 'assets/images/download (11).jpg', 'assets/images/download (11).jpg', 'https://www.youtube.com/watch?v=1kVK0MZlbI4', 'coming_soon', 1),
(7, 'Sikandar', 'sikandar-2025', 1279589, 'An action-packed commercial drama directed by A.R. Murugadoss starring Salman Khan and Rashmika Mandanna, exploring justice and revenge.', 155, '2025-03-30', 'Action, Drama', 'Hindi', 8.0, 'UA', 'assets/images/download (10).jpg', 'assets/images/download (10).jpg', 'https://www.youtube.com/watch?v=0k5fK8A7_eA', 'coming_soon', 0),
(8, 'Dhurandhar: The Revenge', 'dhurandhar-the-revenge', 1317208, 'A high-octane espionage action thriller helmed by Aditya Dhar, following an undercover operative navigating deep enemy territory.', 150, '2026-03-19', 'Action, Thriller', 'Hindi', 8.2, 'A', 'assets/images/download (9).jpg', 'assets/images/download (8).jpg', 'https://www.youtube.com/watch?v=tT8bA_vN97Y', 'coming_soon', 1),
(9, 'Harry Potter and the Goblet of Fire', 'harry-potter-and-the-goblet-of-fire', 674, 'Harry Potter finds himself mysteriously selected to compete in the hazardous Triwizard Tournament between rival magic schools while Lord Voldemort plots his return.', 157, '2005-11-18', 'Fantasy, Adventure', 'English', 7.7, 'UA', 'assets/images/Harry Potter and the Goblet of Fire (2005).jpg', 'assets/images/Harry Potter and the Goblet of Fire (2005).jpg', 'https://www.youtube.com/watch?v=3EGojp4Hh6I', 'now_showing', 0),
(10, 'Harry Potter and the Prisoner of Azkaban', 'harry-potter-and-the-prisoner-of-azkaban', 673, 'Harry Potter, Ron and Hermione return to Hogwarts for their third year of magic, where they delve into the mystery surrounding an escaped prisoner who poses a threat to Harry.', 142, '2004-06-04', 'Fantasy, Adventure', 'English', 7.9, 'UA', 'assets/images/Harry Potter and the Prisoner of Azkaban.jpg', 'assets/images/Harry Potter and the Prisoner of Azkaban.jpg', 'https://www.youtube.com/watch?v=1ZdlAg3j8nI', 'now_showing', 0),
(11, 'Beauty and the Beast', 'beauty-and-the-beast-2017', 321612, 'A selfish Prince is cursed to become a monster for the rest of his life, unless he learns to fall in love with a beautiful young woman he keeps prisoner.', 129, '2017-03-17', 'Fantasy, Musical, Romance', 'English', 7.1, 'U', 'assets/images/Beauty and the Beast 2017 - 13x19 D_S Original Promo Movie Poster _ eBay.jpg', 'assets/images/Beauty and the Beast 2017 - 13x19 D_S Original Promo Movie Poster _ eBay.jpg', 'https://www.youtube.com/watch?v=e3Nl_TCQXuw', 'now_showing', 0),
(12, 'Jumanji: The Next Level', 'jumanji-the-next-level', 512200, 'In Jumanji: The Next Level, the gang is back but the game has changed. As they return to rescue one of their own, the players will have to brave parts unknown.', 123, '2019-12-13', 'Action, Adventure, Comedy', 'English', 6.7, 'UA', 'assets/images/Jumanji Next Level.jpg', 'assets/images/Jumanji Next Level.jpg', 'https://www.youtube.com/watch?v=rBxcF-r9Ibs', 'now_showing', 0),
(13, 'Bajrangi Bhaijaan', 'bajrangi-bhaijaan', 348892, 'An Indian man with a magnanimous heart takes a young mute Pakistani girl back to her homeland to reunite her with her family across the border.', 163, '2015-07-17', 'Action, Comedy, Drama', 'Hindi', 8.1, 'UA', 'assets/images/download (13).jpg', 'assets/images/download (13).jpg', 'https://www.youtube.com/watch?v=vyX4toD395U', 'now_showing', 0),
(14, 'Peaky Blinders: The Immortal Man', 'peaky-blinders-the-immortal-man', 1118283, 'Tommy Shelby returns during World War II in the cinematic conclusion of the iconic Birmingham crime family dynasty, directed by Tom Harper.', 135, '2026-10-01', 'Crime, Drama', 'English', 8.8, 'A', 'assets/images/download (6).jpg', 'assets/images/download (6).jpg', 'https://www.youtube.com/watch?v=oVzVdvGIC7U', 'coming_soon', 1),
(15, 'Maleficent: The Dark Fates', 'maleficent-the-dark-fates', 420809, 'Maleficent and Aurora find themselves facing an ancient force awakening from the deep realm that threatens to shatter the fragile peace between humans and fairies.', 125, '2026-11-20', 'Fantasy, Adventure', 'English', 7.3, 'UA', 'assets/images/download (7).jpg', 'assets/images/download (7).jpg', 'https://www.youtube.com/watch?v=n0OFH4SpAzQ', 'coming_soon', 0),
(16, 'Clash of the Titans', 'clash-of-the-titans', 18823, 'Perseus demigod, son of Zeus, battles the minions of the underworld to stop them from conquering heaven and earth in this Greek mythological adventure.', 106, '2010-04-02', 'Action, Fantasy', 'English', 6.8, 'UA', 'assets/images/download (17).jpg', 'assets/images/download (17).jpg', 'https://www.youtube.com/watch?v=q6CJenNMsb4', 'now_showing', 0),
(17, 'The Scorpion King', 'the-scorpion-king', 9799, 'A desert warrior rises up against the evil army that is destroying his homeland. He captures the enemy\'s key sorcerer and takes her deep into the desert to prepare for a final showdown.', 92, '2002-04-19', 'Action, Adventure', 'English', 6.5, 'UA', 'assets/images/download (16).jpg', 'assets/images/download (16).jpg', 'https://www.youtube.com/watch?v=qX3H_bZgWlI', 'now_showing', 0),
(18, 'Karan Arjun', 'karan-arjun', 40081, 'Karan and Arjun are reincarnated in different parts of the country. Faith in their previous mother\'s prayers brings them together to avenge their death.', 175, '1995-01-13', 'Action, Drama', 'Hindi', 7.2, 'U', 'assets/images/karan arjun.jpg', 'assets/images/karan arjun.jpg', 'https://www.youtube.com/watch?v=f2nNnC9r5_Y', 'now_showing', 0),
(19, 'Kanguva', 'kanguva-2024', 969681, 'A warrior in the prehistoric era must fight to protect his tribe against dangerous forces, while in the modern era a bounty hunter discovers an unexplained cosmic connection.', 154, '2024-11-14', 'Action, Fantasy', 'Tamil', 7.5, 'UA', 'assets/images/Kanguva Movie.jpg', 'assets/images/Kanguva Movie.jpg', 'https://www.youtube.com/watch?v=7u3Q5Z-rB9E', 'now_showing', 0),
(20, 'Wednesday', 'wednesday-the-series', 119051, 'Follows Wednesday Addams\' years as a student, when she attempts to master her emerging psychic ability, thwart and solve the mystery that embroiled her parents.', 110, '2025-10-31', 'Mystery, Comedy, Crime', 'English', 8.1, 'UA', 'assets/images/📸 2000x3000.jpg', 'assets/images/📸 2000x3000.jpg', 'https://www.youtube.com/watch?v=Di310WS8zLk', 'coming_soon', 1);

-- 6. Dynamic Shows (Today and upcoming days)
INSERT IGNORE INTO `shows` (`movie_id`, `theatre_id`, `screen_id`, `show_date`, `show_time`, `regular_price`, `premium_price`, `recliner_price`, `status`) VALUES
(1, 1, 1, CURDATE(), '10:00:00', 220.00, 320.00, 480.00, 'active'),
(1, 1, 1, CURDATE(), '13:45:00', 250.00, 360.00, 520.00, 'active'),
(1, 1, 1, CURDATE(), '17:30:00', 280.00, 390.00, 560.00, 'active'),
(1, 1, 1, CURDATE(), '21:15:00', 280.00, 390.00, 560.00, 'active'),
(2, 1, 2, CURDATE(), '11:00:00', 200.00, 300.00, 450.00, 'active'),
(2, 1, 2, CURDATE(), '15:15:00', 240.00, 340.00, 500.00, 'active'),
(2, 1, 2, CURDATE(), '19:30:00', 260.00, 360.00, 540.00, 'active'),
(3, 2, 3, CURDATE(), '12:30:00', 180.00, 260.00, 420.00, 'active'),
(3, 2, 3, CURDATE(), '16:00:00', 200.00, 280.00, 450.00, 'active'),
(3, 2, 3, CURDATE(), '19:45:00', 220.00, 300.00, 480.00, 'active'),
(4, 3, 4, CURDATE(), '14:00:00', 220.00, 320.00, 480.00, 'active'),
(4, 3, 4, CURDATE(), '18:15:00', 250.00, 350.00, 500.00, 'active'),
(1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '10:00:00', 220.00, 320.00, 480.00, 'active'),
(1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '14:00:00', 250.00, 360.00, 520.00, 'active'),
(1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '18:00:00', 280.00, 390.00, 560.00, 'active'),
(1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '21:30:00', 280.00, 390.00, 560.00, 'active'),
(2, 1, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '13:00:00', 240.00, 340.00, 500.00, 'active'),
(2, 1, 2, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '17:30:00', 260.00, 360.00, 540.00, 'active'),
(3, 2, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', 200.00, 280.00, 450.00, 'active'),
(3, 2, 3, DATE_ADD(CURDATE(), INTERVAL 1 DAY), '20:00:00', 220.00, 300.00, 480.00, 'active');

