# Cloud Architecture Analysis

## Architecture Decision Rationale

### Why Hybrid Cloud (IaaS/PaaS)?
- Combines the flexibility of infrastructure control (EC2) with managed services (RDS, S3)
- Reduces operational overhead while maintaining customization capabilities
- Allows for future scaling without major architectural changes

### Service-Specific Justifications

1. **EC2 (Compute Layer)**
   - PHP/Apache stack for cost-effective web hosting
   - t2.micro or t3.small instance type suitable for initial traffic
   - Auto-scaling capability for traffic spikes during peak shopping periods
   - Estimated capacity: 50-100 concurrent users

2. **S3 (Storage Layer)**
   - Pay-per-use model ideal for growing content
   - Assumption: Average book cover image ~200KB
   - Estimated storage:
     * 10,000 books × 200KB = 2GB for book covers
     * User uploads and profile pictures: ~5GB
     * Static assets: ~1GB
     * Total initial storage: ~8-10GB

3. **RDS (Database Layer)**
   - MySQL on t3.micro instance (cost-effective for small-medium workloads)
   - Estimated database size: 5-10GB
   - Capacity to handle:
     * Up to 50,000 user accounts
     * 10,000 book catalog entries
     * 1,000 daily transactions

4. **CloudFront (CDN)**
   - Reduces load on EC2 instance
   - Improves global access speeds
   - Cost-effective for static content delivery
   - Expected monthly data transfer: 500GB-1TB

## Cost Assumptions (Monthly)

### Essential Services
1. **EC2**: $20-30
   - Single t3.small instance
   - Reserved instance for cost optimization

2. **RDS**: $25-35
   - t3.micro instance
   - 20GB storage
   - Basic backup retention

3. **S3**: $5-10
   - 10GB storage
   - Standard access tier
   - Regular data transfer

4. **CloudFront**: $15-25
   - 500GB-1TB monthly transfer
   - SSL certificate included

### Additional Costs
- Data transfer: $10-20
- Monitoring and CloudWatch: $5-10
- Miscellaneous (backups, snapshots): $10-15

**Total Estimated Monthly Budget: $90-145**

## Scalability Assumptions

### Short-term (6 months)
- Monthly active users: 1,000-5,000
- Daily transactions: 50-100
- Database growth: 1GB/month
- Storage growth: 2GB/month

### Long-term (2 years)
- Monthly active users: 10,000-20,000
- Daily transactions: 200-500
- Database growth: 2-3GB/month
- Storage growth: 5GB/month

## Security Considerations
- SSL/TLS encryption via CloudFront
- RDS encryption at rest
- S3 bucket policies for secure access
- Regular security patches and updates
- Session management for user authentication

## Monitoring and Maintenance
- CloudWatch for performance metrics
- Error logging and debugging capabilities
- Daily automated backups
- Monthly maintenance windows 