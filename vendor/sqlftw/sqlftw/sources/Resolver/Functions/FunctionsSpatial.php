<?php declare(strict_types = 1);
/**
 * This file is part of the SqlFtw library (https://github.com/sqlftw)
 *
 * Copyright (c) 2017 Vlasta Neubauer (@paranoiq)
 *
 * For the full copyright and license information read the file 'license.md', distributed with this source code
 */

// spell-check-ignore: Fréchet MBR WKB WKT geohash

namespace SqlFtw\Resolver\Functions;

trait FunctionsSpatial
{

    // GeomCollection() - Construct geometry collection from geometries
    // GeometryCollection() - Construct geometry collection from geometries
    // LineString() - Construct LineString from Point values
    // MBRContains() - Whether MBR of one geometry contains MBR of another
    // MBRCoveredBy() - Whether one MBR is covered by another
    // MBRCovers() - Whether one MBR covers another
    // MBRDisjoint() - Whether MBRs of two geometries are disjoint
    // MBREquals() - Whether MBRs of two geometries are equal
    // MBRIntersects() - Whether MBRs of two geometries intersect
    // MBROverlaps() - Whether MBRs of two geometries overlap
    // MBRTouches() - Whether MBRs of two geometries touch
    // MBRWithin() - Whether MBR of one geometry is within MBR of another
    // MultiLineString() - Construct MultiLineString from LineString values
    // MultiPoint() - Construct MultiPoint from Point values
    // MultiPolygon() - Construct MultiPolygon from Polygon values
    // Point() - Construct Point from coordinates
    // Polygon() - Construct Polygon from LineString arguments
    // ST_Area() - Return Polygon or MultiPolygon area
    // ST_AsBinary(), ST_AsWKB() - Convert from internal geometry format to WKB
    // ST_AsGeoJSON() - Generate GeoJSON object from geometry
    // ST_AsText(), ST_AsWKT() - Convert from internal geometry format to WKT
    // ST_Buffer() - Return geometry of points within given distance from geometry
    // ST_Buffer_Strategy() - Produce strategy option for ST_Buffer()
    // ST_Centroid() - Return centroid as a point
    // ST_Collect() - Aggregate spatial values into collection - 8.0.24
    // ST_Contains() - Whether one geometry contains another
    // ST_ConvexHull() - Return convex hull of geometry
    // ST_Crosses() - Whether one geometry crosses another
    // ST_Difference() - Return point set difference of two geometries
    // ST_Dimension() - Dimension of geometry
    // ST_Disjoint() - Whether one geometry is disjoint from another
    // ST_Distance() - The distance of one geometry from another
    // ST_Distance_Sphere() - Minimum distance on earth between two geometries
    // ST_EndPoint() - End Point of LineString
    // ST_Envelope() - Return MBR of geometry
    // ST_Equals() - Whether one geometry is equal to another
    // ST_ExteriorRing() - Return exterior ring of Polygon
    // ST_FrechetDistance() - The discrete Fréchet distance of one geometry from another - 8.0.23
    // ST_GeoHash() - Produce a geohash value
    // ST_GeomCollFromText(), ST_GeometryCollectionFromText(), ST_GeomCollFromTxt() - Return geometry collection from WKT
    // ST_GeomCollFromWKB(), ST_GeometryCollectionFromWKB() - Return geometry collection from WKB
    // ST_GeometryN() - Return N-th geometry from geometry collection
    // ST_GeometryType() - Return name of geometry type
    // ST_GeomFromGeoJSON() - Generate geometry from GeoJSON object
    // ST_GeomFromText(), ST_GeometryFromText() - Return geometry from WKT
    // ST_GeomFromWKB(), ST_GeometryFromWKB() - Return geometry from WKB
    // ST_HausdorffDistance() - The discrete Hausdorff distance of one geometry from another - 8.0.23
    // ST_InteriorRingN() - Return N-th interior ring of Polygon
    // ST_Intersection() - Return point set intersection of two geometries
    // ST_Intersects() - Whether one geometry intersects another
    // ST_IsClosed() - Whether a geometry is closed and simple
    // ST_IsEmpty() - Whether a geometry is empty
    // ST_IsSimple() - Whether a geometry is simple
    // ST_IsValid() - Whether a geometry is valid
    // ST_LatFromGeoHash() - Return latitude from geohash value
    // ST_Latitude() - Return latitude of Point - 8.0.12
    // ST_Length() - Return length of LineString
    // ST_LineFromText(), ST_LineStringFromText() - Construct LineString from WKT
    // ST_LineFromWKB(), ST_LineStringFromWKB() - Construct LineString from WKB
    // ST_LineInterpolatePoint() - The point a given percentage along a LineString - 8.0.24
    // ST_LineInterpolatePoints() - The points a given percentage along a LineString - 8.0.24
    // ST_LongFromGeoHash() - Return longitude from geohash value
    // ST_Longitude() - Return longitude of Point - 8.0.12
    // ST_MakeEnvelope() - Rectangle around two points
    // ST_MLineFromText(), ST_MultiLineStringFromText() - Construct MultiLineString from WKT
    // ST_MLineFromWKB(), ST_MultiLineStringFromWKB() - Construct MultiLineString from WKB
    // ST_MPointFromText(), ST_MultiPointFromText() - Construct MultiPoint from WKT
    // ST_MPointFromWKB(), ST_MultiPointFromWKB() - Construct MultiPoint from WKB
    // ST_MPolyFromText(), ST_MultiPolygonFromText() - Construct MultiPolygon from WKT
    // ST_MPolyFromWKB(), ST_MultiPolygonFromWKB() - Construct MultiPolygon from WKB
    // ST_NumGeometries() - Return number of geometries in geometry collection
    // ST_NumInteriorRing(), ST_NumInteriorRings() - Return number of interior rings in Polygon
    // ST_NumPoints() - Return number of points in LineString
    // ST_Overlaps() - Whether one geometry overlaps another
    // ST_PointAtDistance() - The point a given distance along a LineString - 8.0.24
    // ST_PointFromGeoHash() - Convert geohash value to POINT value
    // ST_PointFromText() - Construct Point from WKT
    // ST_PointFromWKB() - Construct Point from WKB
    // ST_PointN() - Return N-th point from LineString
    // ST_PolyFromText(), ST_PolygonFromText() - Construct Polygon from WKT
    // ST_PolyFromWKB(), ST_PolygonFromWKB() - Construct Polygon from WKB
    // ST_Simplify() - Return simplified geometry
    // ST_SRID() - Return spatial reference system ID for geometry
    // ST_StartPoint() - Start Point of LineString
    // ST_SwapXY() - Return argument with X/Y coordinates swapped
    // ST_SymDifference() - Return point set symmetric difference of two geometries
    // ST_Touches() - Whether one geometry touches another
    // ST_Transform() - Transform coordinates of geometry - 8.0.13
    // ST_Union() - Return point set union of two geometries
    // ST_Validate() - Return validated geometry
    // ST_Within() - Whether one geometry is within another
    // ST_X() - Return X coordinate of Point
    // ST_Y() - Return Y coordinate of Point

}
